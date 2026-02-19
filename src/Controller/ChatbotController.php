<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ChatbotController extends AbstractController
{
    public function __construct(
        private ChatbotService $chatbotService,
    ) {
    }

    #[Route('/api/chatbot', name: 'api_chatbot', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['messages']) || !is_array($data['messages'])) {
                return $this->json([
                    'error' => 'Le champ "messages" est requis et doit être un tableau.',
                ], 400);
            }

            // Garder seulement role + content
            $messages = array_map(function ($msg) {
                return [
                    'role' => $msg['role'] ?? 'user',
                    'content' => $msg['content'] ?? '',
                ];
            }, $data['messages']);

            if (empty($messages)) {
                return $this->json([
                    'error' => 'Au moins un message est requis.',
                ], 400);
            }

            $response = $this->chatbotService->chat($messages);

            return $this->json($response);
        } catch (\Exception $e) {
            $statusCode = 500;
            $errorMessage = 'Une erreur est survenue lors du traitement de votre message.';

            // Erreurs API OpenAI courantes
            if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), 'Unauthorized')) {
                $errorMessage = 'Clé API OpenAI invalide ou manquante. Vérifiez votre configuration.';
                $statusCode = 401;
            } elseif (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'Rate limit')) {
                $errorMessage = 'Trop de requêtes. Veuillez réessayer dans quelques instants.';
                $statusCode = 429;
            }

            return $this->json([
                'error' => $errorMessage,
            ], $statusCode);
        }
    }
}
