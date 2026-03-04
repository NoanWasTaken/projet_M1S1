<?php
namespace App\Controller;

use App\Entity\PromoCode;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CpsTestController extends AbstractController
{
    #[Route('/api/cps-test/claim', name: 'api_cps_test_claim', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function claim(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        $clicks = (int)($data['clicks'] ?? 0);
        $required = 100;
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['ok' => false, 'message' => 'Non authentifié.'], 401);
        }
        if ($clicks < $required) {
            return $this->json(['ok' => false, 'message' => 'Score insuffisant.'], 200);
        }
        $promoCodeRepo = $em->getRepository(PromoCode::class);
        $promoCode = $promoCodeRepo->findOneBy(['code' => 'PROMO-100CLICKS']);
        if (!$promoCode) {
            $promoCode = new PromoCode();
            $promoCode->setCode('PROMO-100CLICKS');
            $promoCode->setDescription('Débloqué via le test CPS souris');
            $promoCode->setActive(true);
            $promoCode->setUnlockCondition('CPS_100CLICKS');
            $em->persist($promoCode);
        }
        if (!$user->getPromoCodes()->contains($promoCode)) {
            $user->addPromoCode($promoCode);
            $promoCode->addUser($user);
        }
        $em->persist($user);
        $em->persist($promoCode);
        $em->flush();
        return $this->json([
            'ok' => true,
            'promoCode' => $promoCode->getCode(),
            'message' => 'Code promo débloqué !',
        ]);
    }
}
