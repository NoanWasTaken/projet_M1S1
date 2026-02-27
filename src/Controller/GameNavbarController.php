<?php

namespace App\Controller;

use App\Entity\XPEvent;
use App\Entity\UserReward;
use App\Repository\PlayerProfileRepository;
use App\Repository\RewardRepository;
use App\Repository\UserRewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GameNavbarController extends AbstractController
{
    #[Route('/game/navbar/claim', name: 'app_game_navbar_claim', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function claim(
        Request $request,
        EntityManagerInterface $em,
        PlayerProfileRepository $profileRepo,
        RewardRepository $rewardRepo,
        UserRewardRepository $userRewardRepo,
    ): JsonResponse {
        $payload = json_decode($request->getContent() ?: '[]', true);

        $order = $payload['order'] ?? [];
        $target = ["clavier", "souris", "casque", "chaise"];

        if (!is_array($order) || $order !== $target) {
            return $this->json(['message' => 'Ordre incorrect.'], 400);
        }

        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['owner' => $user]);

        if (!$profile) {
            return $this->json(['message' => 'Profil joueur introuvable.'], 400);
        }

        $reward = $rewardRepo->findOneBy(['code' => 'NAVBAR_ORDER_1']);
        if (!$reward || !$reward->isActive()) {
            return $this->json(['message' => 'Récompense indisponible.'], 400);
        }

        $already = $userRewardRepo->findOneBy(['profile' => $profile, 'reward' => $reward]);
        if ($already) {
            return $this->json([
                'awarded' => false,
                'xpTotal' => $profile->getXpTotal(),
                'level' => $profile->getLevel(),
                'message' => 'Déjà validé.',
            ]);
        }

        $ur = new UserReward();
        $ur->setProfile($profile);
        $ur->setReward($reward);
        $ur->setUnlockedAt(new \DateTimeImmutable());
        $ur->setSource('NAVBAR_GAME');
        $ur->setMeta(['order' => $order]);

        $xp = 800;

        $event = new XPEvent();
        $event->setProfile($profile);
        $event->setAmount($xp);
        $event->setReason('NAVBAR_ORDER');
        $event->setMeta(['order' => $order]);
        $event->setCreatedAt(new \DateTimeImmutable());

        $newTotal = $profile->getXpTotal() + $xp;
        $profile->setXpTotal($newTotal);

        $newLevel = intdiv($newTotal, 1000) + 1;
        $profile->setLevel($newLevel);
        $profile->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($ur);
        $em->persist($event);
        $em->flush();

        return $this->json([
            'awarded' => true,
            'xpTotal' => $profile->getXpTotal(),
            'level' => $profile->getLevel(),
            'message' => '+800 XP ! Combo validé.',
        ]);
    }
}