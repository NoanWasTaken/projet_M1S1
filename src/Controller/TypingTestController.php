<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\XPEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TypingTestController extends AbstractController
{
    #[Route('/api/typing-test/recommendation', name: 'api_typing_test_recommendation', methods: ['POST'])]
    public function recommendation(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '{}', true);

        $wpm = isset($payload['wpm']) ? (float) $payload['wpm'] : null;
        $accuracy = isset($payload['accuracy']) ? (float) $payload['accuracy'] : null;

        if ($wpm === null || $wpm < 0) {
            return $this->json(['error' => 'Score invalide'], 400);
        }

        $gameTypes = match (true) {
            $wpm >= 70 => ['FPS', 'Battle Royale'],
            $wpm >= 50 => ['MOBA', 'RPG'],
            default    => ['MMO', 'RPG'],
        };

        $accTxt = $accuracy === null ? 'non mesurée' : (string) round($accuracy * 100) . '%';
        $messages = [[
            'role' => 'user',
            'content' =>
                "Je suis sur GearForge (catégorie Clavier) et je viens de faire un mini-jeu 'test clavier'.\n" .
                "Score: {$wpm} WPM. Précision: {$accTxt}.\n" .
                "Je veux une recommandation de Clavier (catégorie: Clavier) dans le catalogue GearForge.\n" .
                "Choisis un clavier principal + 1 alternative, adaptés à ces usages: " . implode(', ', $gameTypes) . ".\n" .
                "Contraintes:\n" .
                "- Utilise le tool recommend_products_by_game_type avec category='Clavier'.\n" .
                "- Si aucun clavier n'est trouvé, explique pourquoi et propose un autre game type proche.\n"
        ]];

        $assistant = $chatbotService->chat($messages);

        return $this->json([
            'content' => $assistant['content'] ?? 'Désolé, je n’ai pas pu générer de recommandation.',
            'meta' => [
                'wpm' => $wpm,
                'accuracy' => $accuracy,
                'game_types' => $gameTypes,
            ],
        ]);
    }

    #[Route('/api/typing-test/claim-xp', name: 'api_typing_test_claim_xp', methods: ['POST'])]
    public function claimXp(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Connecte-toi pour réclamer ta récompense.'], 401);
        }

        $profile = $user->getPlayerProfile();
        if (!$profile) {
            return $this->json(['message' => 'Profil joueur introuvable.'], 400);
        }

        $payload = json_decode($request->getContent() ?: '{}', true);
        $wpm = isset($payload['wpm']) ? (float) $payload['wpm'] : 0.0;

        if ($wpm < 45) {
            return $this->json(['message' => 'Score insuffisant pour réclamer la récompense.'], 400);
        }

        // Anti double-claim: on check si déjà réclamé
        $existing = $em->getRepository(XPEvent::class)->findOneBy([
            'profile' => $profile,
            'reason' => 'typing_test_45wpm',
        ]);

        if ($existing) {
            return $this->json([
                'ok' => true,
                'awarded' => false,
                'message' => 'Récompense déjà réclamée.',
            ]);
        }

        $event = new XPEvent();
        $event->setProfile($profile);
        $event->setAmount(650);
        $event->setReason('typing_test_45wpm');
        $event->setCreatedAt(new \DateTimeImmutable());

        $em->persist($event);

        // IMPORTANT: ici, selon ton modèle, il faut aussi incrémenter le total XP du profile
        // Exemple (si tu as $profile->addXp(650) ou setXpTotal):
        // $profile->addXp(650);

        $em->flush();

        return $this->json([
            'ok' => true,
            'awarded' => true,
            'level' => method_exists($profile, 'getLevel') ? $profile->getLevel() : null,
            'xpTotal' => method_exists($profile, 'getXp') ? $profile->getXp() : null,
        ]);
    }
}