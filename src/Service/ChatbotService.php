<?php

namespace App\Service;

use App\Repository\ProductsRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        private ProductsRepository $productsRepository,
        private string $openaiApiKey,
        private string $openaiModel,
        private string $chatbotSystemMessage,
        private LoggerInterface $logger,
    ) {}

    // Point d'entrée principal — tool calling loop
    public function chat(array $messages): array
    {
        set_time_limit(120); // 2 appels OpenAI séquentiels + reasoning tokens

        $this->logger->info('[Chatbot] Début du traitement', [
            'model' => $this->openaiModel,
            'history_length' => count($messages),
        ]);

        $fullMessages = array_merge(
            [['role' => 'system', 'content' => $this->chatbotSystemMessage]],
            $messages
        );

        $this->logger->info('[Chatbot] Envoi requête initiale à OpenAI');
        $response = $this->callOpenAI($fullMessages);

        $this->logger->info('[Chatbot] Réponse initiale reçue', [
            'finish_reason' => $response['choices'][0]['finish_reason'] ?? 'unknown',
        ]);

        $maxIterations = 5;
        $iteration = 0;
        $collectedProductSummaries = [];
        $hasAnyData = false;

        while (
            isset($response['choices'][0]['finish_reason'])
            && $response['choices'][0]['finish_reason'] === 'tool_calls'
            && $iteration < $maxIterations
        ) {
            $assistantMessage = $response['choices'][0]['message'];
            $toolCalls = $assistantMessage['tool_calls'] ?? [];
            $fullMessages[] = $assistantMessage;

            $this->logger->info('[Chatbot] Tool calls détectés', [
                'iteration' => $iteration,
                'tool_count' => count($toolCalls),
            ]);

            foreach ($toolCalls as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true) ?? [];

                $this->logger->info('[Chatbot] Exécution du tool', [
                    'function' => $functionName,
                    'arguments' => $arguments,
                ]);

                $result = $this->executeToolCall($functionName, $arguments);

                $productCount = count($result['products'] ?? []);
                $this->logger->info('[Chatbot] Résultat du tool', [
                    'function' => $functionName,
                    'products_found' => $productCount,
                    'message' => $result['message'] ?? null,
                ]);

                // Collecter pour fallback + tracker si données réelles
                if (isset($result['products']) && is_array($result['products']) && count($result['products']) > 0) {
                    $hasAnyData = true;
                    foreach ($result['products'] as $p) {
                        $collectedProductSummaries[] = sprintf(
                            '%s (%s, %s) — %s€ — note %s — %s',
                            $p['name'] ?? '?',
                            $p['category'] ?? '?',
                            $p['brand'] ?? '?',
                            $p['price'] ?? '?',
                            $p['rating'] ?? '?',
                            $p['description'] ?? ''
                        );
                    }
                } elseif (isset($result['comparison']) && is_array($result['comparison']) && count($result['comparison']) > 0) {
                    $hasAnyData = true;
                    $collectedProductSummaries[] = $result['message'] ?? '';
                } elseif (isset($result['error'])) {
                    $hasAnyData = true; // erreur explicite à relayer
                    $collectedProductSummaries[] = $result['message'] ?? $result['error'];
                } elseif (isset($result['message'])) {
                    $hasAnyData = true;
                    $collectedProductSummaries[] = $result['message'];
                }

                $fullMessages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content' => $this->formatToolResultAsText($functionName, $result),
                ];
            }

            // Court-circuit : pas de résultats → réponse directe
            if (!$hasAnyData) {
                $this->logger->info('[Chatbot] Aucun produit trouvé, réponse directe');
                return [
                    'role' => 'assistant',
                    'content' => 'Désolé, nous n\'avons pas de produit correspondant à cette recherche dans notre catalogue GearForge. 🎮 N\'hésitez pas à essayer un autre type de jeu ou à ajuster votre budget.',
                ];
            }

            $this->logger->info('[Chatbot] Envoi requête de synthèse (forceText)', ['iteration' => $iteration]);
            $response = $this->callOpenAI($fullMessages, forceText: true);

            $content = $response['choices'][0]['message']['content'] ?? null;
            $this->logger->info('[Chatbot] Réponse de synthèse reçue', [
                'finish_reason' => $response['choices'][0]['finish_reason'] ?? 'unknown',
                'content_null' => $content === null,
            ]);

            $iteration++;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;

        // Fallback gpt-5-nano : null après tool call
        if ($content === null || trim($content) === '') {
            $this->logger->warning('[Chatbot] Content null, fallback minimal', [
                'iteration' => $iteration,
                'summaries_count' => count($collectedProductSummaries),
            ]);
            $content = $this->fallbackWithMinimalContext($messages, $collectedProductSummaries);
        }

        if ($content === null || trim($content) === '') {
            $this->logger->error('[Chatbot] Échec total');
            $content = 'Désolé, je n\'ai pas pu générer de réponse.';
        } else {
            $this->logger->info('[Chatbot] Réponse finale générée', ['content_length' => strlen($content)]);
        }

        return [
            'role' => 'assistant',
            'content' => $content,
        ];
    }

    // Convertit le résultat tool en texte lisible — évite l'écho JSON
    private function formatToolResultAsText(string $functionName, array $result): string
    {
        $header = "=== DONNÉES OFFICIELLES GEARFORGE (base de données) ===\n"
            . "INSTRUCTION CRITIQUE : Utilise UNIQUEMENT les informations ci-dessous pour ta réponse.\n"
            . "N'utilise JAMAIS tes connaissances générales sur les produits. Toutes les specs, prix et notes viennent exclusivement de ces données.\n\n";

        if (isset($result['error'])) {
            return $result['message'] ?? $result['error'];
        }

        if ($functionName === 'compare_products') {
            $lines = [];
            $lines[] = $result['message'] ?? '';
            $lines[] = 'Contexte : ' . ($result['context'] ?? 'non précisé');
            $lines[] = 'Gagnant recommandé : ' . ($result['winner'] ?? 'indéterminé');
            $lines[] = '';
            foreach ($result['comparison'] ?? [] as $p) {
                $lines[] = '--- ' . $p['name'] . ' ---';
                $lines[] = 'Catégorie : ' . ($p['category'] ?? '?');
                $lines[] = 'Marque : ' . ($p['brand'] ?? '?');
                $lines[] = 'Prix : ' . ($p['price'] ?? '?') . '€';
                $lines[] = 'Note : ' . ($p['rating'] ?? '?') . '/5';
                $lines[] = 'Types de jeux couverts : ' . implode(', ', $p['all_game_types'] ?? []);
                $lines[] = 'Types correspondant à votre demande : ' . implode(', ', $p['matching_game_types'] ?? []);
                $lines[] = 'Description : ' . ($p['description'] ?? '');
                if (!empty($p['specifications'])) {
                    $lines[] = 'Spécifications : ' . (is_array($p['specifications'])
                        ? implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($p['specifications']), $p['specifications']))
                        : $p['specifications']);
                }
                $lines[] = '';
            }
            return $header . implode("\n", $lines);
        }

        $lines = [];
        if (isset($result['message'])) {
            $lines[] = $result['message'];
        }
        foreach ($result['products'] ?? [] as $p) {
            $gameTypes = implode(', ', $p['game_types'] ?? []);
            $lines[] = sprintf(
                '- %s (%s, %s) — %s€ — note %s/5 — types: %s — %s',
                $p['name'] ?? '?',
                $p['category'] ?? '?',
                $p['brand'] ?? '?',
                $p['price'] ?? '?',
                $p['rating'] ?? '?',
                $gameTypes ?: 'non précisé',
                $p['description'] ?? ''
            );
            if (!empty($p['specifications'])) {
                $specs = is_array($p['specifications'])
                    ? implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($p['specifications']), $p['specifications']))
                    : $p['specifications'];
                $lines[] = '  Spécifications : ' . $specs;
            }
        }
        return $header . (implode("\n", $lines) ?: json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    // Fallback gpt-5-nano : contexte propre sans role:tool, injecte les produits dans le dernier message user
    private function fallbackWithMinimalContext(array $originalMessages, array $productSummaries): ?string
    {
        $cleanHistory = array_values(
            array_filter($originalMessages, fn($m) => in_array($m['role'] ?? '', ['user', 'assistant']))
        );

        for ($i = count($cleanHistory) - 1; $i >= 0; $i--) {
            if ($cleanHistory[$i]['role'] === 'user') {
                if (!empty($productSummaries)) {
                    $cleanHistory[$i]['content'] .= "\n\n[Résultats du catalogue GearForge]\n" . implode("\n", $productSummaries);
                }
                break;
            }
        }

        $minimalMessages = array_merge(
            [['role' => 'system', 'content' => $this->chatbotSystemMessage]],
            $cleanHistory
        );

        $this->logger->info('[Chatbot] Fallback: envoi contexte minimal', [
            'messages_count' => count($minimalMessages),
            'summaries_injected' => count($productSummaries),
        ]);

        $response = $this->callOpenAI($minimalMessages, forceText: false);
        $content = $response['choices'][0]['message']['content'] ?? null;

        if ($content === null || trim($content) === '') {
            $this->logger->warning('[Chatbot] Fallback: encore null, dernier essai sans tools');
            $response = $this->callOpenAI($minimalMessages, forceText: true);
            $content = $response['choices'][0]['message']['content'] ?? null;
        }

        $this->logger->info('[Chatbot] Fallback: résultat', ['content_null' => $content === null]);

        return $content;
    }

    // Appel OpenAI — forceText=true : ommet les tools
    private function callOpenAI(array $messages, bool $forceText = false): array
    {
        $this->logger->debug('[Chatbot] callOpenAI', [
            'force_text' => $forceText,
            'messages_count' => count($messages),
            'roles' => array_column($messages, 'role'),
        ]);

        $payload = [
            'model' => $this->openaiModel,
            'messages' => $messages,
            'max_completion_tokens' => 4096,
        ];

        if (!$forceText) {
            $payload['tools'] = $this->getToolDefinitions();
            $payload['tool_choice'] = 'auto';
        }

        $response = $this->httpClient->request('POST', self::OPENAI_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $data = $response->toArray();

        $this->logger->debug('[Chatbot] callOpenAI réponse', [
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? 'unknown',
            'content_null' => ($data['choices'][0]['message']['content'] ?? null) === null,
            'has_tool_calls' => isset($data['choices'][0]['message']['tool_calls']),
            'usage' => $data['usage'] ?? null,
        ]);

        return $data;
    }

    private function executeToolCall(string $functionName, array $arguments): array
    {
        return match ($functionName) {
            'recommend_products_by_game_type' => $this->toolRecommendProducts($arguments),
            'compare_products' => $this->toolCompareProducts($arguments),
            default => ['error' => "Tool inconnu : $functionName"],
        };
    }

    // Tool : recommandation par game types + filtres
    private function toolRecommendProducts(array $arguments): array
    {
        $gameTypes = $arguments['game_types'] ?? [];
        $maxPrice = isset($arguments['max_price']) ? (float) $arguments['max_price'] : null;
        $minPrice = isset($arguments['min_price']) ? (float) $arguments['min_price'] : null;
        $category = $arguments['category'] ?? null;

        if (empty($gameTypes)) {
            return ['error' => 'Aucun type de jeu fourni'];
        }

        $products = $this->productsRepository->findBestByGameTypes($gameTypes, $maxPrice, $minPrice, $category);

        if (empty($products) && $category) {
            $products = $this->productsRepository->findTopByCategory($category, $maxPrice, $minPrice);
        }

        if (empty($products)) {
            $filters = [];
            if ($category !== null) $filters[] = 'catégorie ' . $category;
            if ($maxPrice !== null) $filters[] = 'prix max ' . $maxPrice . '€';
            if ($minPrice !== null) $filters[] = 'prix min ' . $minPrice . '€';
            $message = 'Aucun produit trouvé pour les types de jeux : ' . implode(', ', $gameTypes);
            if (!empty($filters)) $message .= ' (' . implode(', ', $filters) . ')';
            return [
                'message' => $message,
                'products' => [],
            ];
        }

        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'category' => $product->getCategory(),
                'brand' => $product->getBrand(),
                'price' => $product->getPrice(),
                'rating' => $product->getRating(),
                'description' => $product->getDescription(),
                'specifications' => $product->getSpecifications(),
                'game_types' => array_map(
                    fn($gt) => $gt->getType(),
                    $product->getGameTypes()->toArray()
                ),
            ];
        }

        return [
            'message' => 'Produits recommandés pour : ' . implode(', ', $gameTypes),
            'products' => $result,
        ];
    }

    // Tool : comparaison multi-produits — même catégorie obligatoire
    private function toolCompareProducts(array $arguments): array
    {
        $productNames = $arguments['product_names'] ?? [];
        $gameTypes = $arguments['game_types'] ?? [];

        if (empty($productNames)) {
            return ['error' => 'Aucun nom de produit fourni pour la comparaison'];
        }

        $products = $this->productsRepository->findByNames($productNames);

        if (empty($products)) {
            return [
                'message' => 'Aucun produit trouvé pour les noms : ' . implode(', ', $productNames),
                'comparison' => [],
            ];
        }

        // Refus si catégories différentes
        $categories = array_unique(array_map(fn($p) => strtolower($p->getCategory() ?? ''), $products));
        if (count($categories) > 1) {
            return [
                'error' => 'categories_mismatch',
                'message' => 'Impossible de comparer des produits de catégories différentes : '
                    . implode(', ', array_map(fn($p) => $p->getName() . ' (' . $p->getCategory() . ')', $products)),
            ];
        }

        $comparison = [];
        foreach ($products as $product) {
            $productGameTypes = array_map(
                fn($gt) => $gt->getType(),
                $product->getGameTypes()->toArray()
            );

            $matchingTypes = empty($gameTypes)
                ? $productGameTypes
                : array_values(array_intersect($gameTypes, $productGameTypes));

            $matchScore = count($matchingTypes);

            $comparison[] = [
                'name' => $product->getName(),
                'category' => $product->getCategory(),
                'brand' => $product->getBrand(),
                'price' => $product->getPrice(),
                'rating' => $product->getRating(),
                'description' => $product->getDescription(),
                'specifications' => $product->getSpecifications(),
                'all_game_types' => $productGameTypes,
                'matching_game_types' => $matchingTypes,
                'match_score' => $matchScore,
            ];
        }

        // matchScore DESC, rating DESC
        usort($comparison, fn($a, $b) =>
            $b['match_score'] !== $a['match_score']
                ? $b['match_score'] - $a['match_score']
                : (float) $b['rating'] <=> (float) $a['rating']
        );

        $winner = $comparison[0];
        $context = empty($gameTypes) ? 'usage général' : implode(' + ', $gameTypes);

        return [
            'message' => "Comparaison pour $context : le meilleur est {$winner['name']} (note : {$winner['rating']})",
            'winner' => $winner['name'],
            'context' => $context,
            'comparison' => $comparison,
        ];
    }

    // Définitions tools OpenAI
    private function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'recommend_products_by_game_type',
                    'description' => 'Recherche et recommande les meilleurs produits gaming (souris, claviers, casques, tapis) adaptés à des types de jeux spécifiques. Retourne les données complètes : prix, stock, note, spécifications techniques détaillées. Utilise ce tool quand l\'utilisateur mentionne un type de jeu (FPS, RPG, MOBA, etc.) ou demande des recommandations. Utilise les filtres de prix si un budget est mentionné, et category pour filtrer par type de produit.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'game_types' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Types de jeux. Exemples : "FPS", "RPG", "MOBA", "MMO", "Racing", "Battle Royale".',
                            ],
                            'max_price' => [
                                'type' => 'number',
                                'description' => 'Prix maximum en euros. Exemples : 50 pour "moins de 50€", "sous 50 euros", "budget 50€".',
                            ],
                            'min_price' => [
                                'type' => 'number',
                                'description' => 'Prix minimum en euros. Exemple : 100 pour "au dessus de 100€".',
                            ],
                            'category' => [
                                'type' => 'string',
                                'description' => 'Filtrer par catégorie de produit. Exemples : "Souris", "Clavier", "Casque", "Tapis".',
                            ],
                        ],
                        'required' => ['game_types'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'compare_products',
                    'description' => 'Compare deux ou plusieurs produits gaming de la même catégorie en mettant en avant leurs compatibilités avec les types de jeux de l\'utilisateur. Désigne le gagnant selon le nombre de game types couverts puis la note. Utilise ce tool quand l\'utilisateur demande une comparaison entre des produits spécifiques (ex: "compare la DeathAdder et la Rival 3", "laquelle des deux est mieux pour FPS+RPG").',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_names' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Noms (partiels ou complets) des produits à comparer. Exemples : ["DeathAdder V3", "Rival 3"].',
                            ],
                            'game_types' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Types de jeux pratiqués par l\'utilisateur pour évaluer la compatibilité. Exemples : ["FPS", "RPG"].',
                            ],
                        ],
                        'required' => ['product_names'],
                    ],
                ],
            ],
        ];
    }
}
