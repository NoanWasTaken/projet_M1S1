<?php

namespace App\Controller;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Entity\User;
use App\Repository\ChatConversationRepository;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ChatbotController extends AbstractController
{
    public function __construct(
        private ChatbotService $chatbotService,
        private EntityManagerInterface $em,
        private ChatConversationRepository $conversationRepository,
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


            $user = $this->getUser();
            if ($user instanceof User) {
                $conversationId = isset($data['conversationId']) ? (int) $data['conversationId'] : null;

                $conversation = null;
                if ($conversationId) {
                    $conversation = $this->conversationRepository->find($conversationId);
                    if ($conversation && $conversation->getUser() !== $user) {
                        $conversation = null;
                    }
                }

                if ($conversation === null) {
                    $conversation = new ChatConversation();
                    $conversation->setUser($user);
                    $this->em->persist($conversation);
                }

                $lastUserMessage = null;
                foreach (array_reverse($messages) as $msg) {
                    if ($msg['role'] === 'user') {
                        $lastUserMessage = $msg['content'];
                        break;
                    }
                }

                if ($lastUserMessage !== null) {
                    $userMsg = new ChatMessage($conversation, 'user', $lastUserMessage);
                    $this->em->persist($userMsg);
                }

                if (!isset($response['error'])) {
                    $assistantContent = $response['content'] ?? '';
                    $assistantMsg = new ChatMessage($conversation, 'assistant', $assistantContent);
                    $this->em->persist($assistantMsg);
                }

                $conversation->touch();
                $this->em->flush();

                $response['conversationId'] = $conversation->getId();
            }

            return $this->json($response);
        } catch (\Exception $e) {
            $statusCode = 500;
            $errorMessage = 'Une erreur est survenue lors du traitement de votre message.';

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

