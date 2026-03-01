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

final class GameFooterWordController extends AbstractController
{
    private const TARGET_WORD = 'ENFANT';
    private const REWARD_CODE = 'FOOTER_WORD_ENFANT';
    private const ATTEMPT_REASON = 'FOOTER_WORD_ATTEMPT';
    private const XP_REWARD = 1000;
    private const DAILY_TRIES = 5;

    #[Route('/game/footer-word/claim', name: 'app_game_footer_word_claim', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function claim(
        Request $request,
        EntityManagerInterface $em,
        PlayerProfileRepository $profileRepo,
        RewardRepository $rewardRepo,
        UserRewardRepository $userRewardRepo,
    ): JsonResponse {
        $payload = json_decode($request->getContent() ?: '[]', true);
        $word = strtoupper((string) ($payload['word'] ?? ''));

        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['owner' => $user]); 

        if (!$profile) {
            return $this->json(['message' => 'Profil joueur introuvable.'], 400);
        }

        $tz = new \DateTimeZone('Europe/Paris');
        $start = new \DateTimeImmutable('today', $tz);
        $end = $start->modify('+1 day');

        $attemptsUsed = (int) $em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(XPEvent::class, 'e')
            ->where('e.profile = :profile')
            ->andWhere('e.reason = :reason')
            ->andWhere('e.createdAt >= :start AND e.createdAt < :end')
            ->setParameter('profile', $profile)
            ->setParameter('reason', self::ATTEMPT_REASON)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        $triesRemainingBefore = max(0, self::DAILY_TRIES - $attemptsUsed);

        // plus d'essais restant 
        if ($triesRemainingBefore <= 0) {
            return $this->json([
                'ok' => false,
                'awarded' => false,
                'triesRemaining' => 0,
                'message' => 'Plus d’essais pour aujourd’hui. Reviens demain.',
            ], 429);
        }

        // si déjà validé 
        $reward = $rewardRepo->findOneBy(['code' => self::REWARD_CODE]);
        if (!$reward || !$reward->isActive()) {
            return $this->json([
                'ok' => false,
                'awarded' => false,
                'triesRemaining' => $triesRemainingBefore,
                'message' => 'Récompense indisponible.',
            ], 400);
        }

        $already = $userRewardRepo->findOneBy(['profile' => $profile, 'reward' => $reward]);
        if ($already) {
            $meta = $already->getMeta() ?? [];
            return $this->json([
                'ok' => true,
                'awarded' => false,
                'triesRemaining' => $triesRemainingBefore,
                'xpTotal' => $profile->getXpTotal(),
                'level' => $profile->getLevel(),
                'couponCode' => $meta['couponCode'] ?? null,
                'discountLabel' => $meta['discountLabel'] ?? '—',
                'message' => 'Déjà validé.',
            ], 200);
        }

        $isCorrect = ($word === self::TARGET_WORD);

        $attemptEvent = new XPEvent();
        $attemptEvent->setProfile($profile);
        $attemptEvent->setAmount(0);
        $attemptEvent->setReason(self::ATTEMPT_REASON);
        $attemptEvent->setMeta([
            'word' => $word,
            'ok' => $isCorrect,
        ]);
        $attemptEvent->setCreatedAt(new \DateTimeImmutable('now', $tz));

        $em->persist($attemptEvent);
        $em->flush(); // on sécurise la consommation d'essai même si la suite plante

        $triesRemainingAfter = max(0, $triesRemainingBefore - 1);

        //faux
        if (!$isCorrect) {
            return $this->json([
                'ok' => false,
                'awarded' => false,
                'triesRemaining' => $triesRemainingAfter,
                'message' => 'Mot incorrect.',
            ], 200);
        }

        //vrai 
        $couponCode = $this->generateCouponCode('GF');
        $discountLabel = '-10%';

        $ur = new UserReward();
        $ur->setProfile($profile);
        $ur->setReward($reward);
        $ur->setUnlockedAt(new \DateTimeImmutable('now', $tz));
        $ur->setSource('FOOTER_WORD_GAME');
        $ur->setMeta([
            'word' => self::TARGET_WORD,
            'couponCode' => $couponCode,
            'discountLabel' => $discountLabel,
        ]);

        $xpEvent = new XPEvent();
        $xpEvent->setProfile($profile);
        $xpEvent->setAmount(self::XP_REWARD);
        $xpEvent->setReason('FOOTER_WORD_ENFANT');
        $xpEvent->setMeta([
            'word' => self::TARGET_WORD,
            'couponCode' => $couponCode,
        ]);
        $xpEvent->setCreatedAt(new \DateTimeImmutable('now', $tz));

        $newTotal = $profile->getXpTotal() + self::XP_REWARD;
        $profile->setXpTotal($newTotal);
        $profile->setLevel(intdiv($newTotal, 1000) + 1);
        $profile->setUpdatedAt(new \DateTimeImmutable('now', $tz));

        $em->persist($ur);
        $em->persist($xpEvent);
        $em->flush();

        return $this->json([
            'ok' => true,
            'awarded' => true,
            'triesRemaining' => $triesRemainingAfter,
            'xpTotal' => $profile->getXpTotal(),
            'level' => $profile->getLevel(),
            'couponCode' => $couponCode,
            'discountLabel' => $discountLabel,
            'message' => '+1000 XP ! Coupon débloqué.',
        ], 200);
    }

    private function generateCouponCode(string $prefix): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $buf = '';
        for ($i = 0; $i < 6; $i++) {
            $buf .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $prefix . '-' . $buf;
    }
}