<?php

namespace App\Controller;

use App\Repository\PlayerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileSkinController extends AbstractController
{
    #[Route('/profile/skin/hair', name: 'app_profile_skin_hair_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveHair(
        Request $request,
        PlayerProfileRepository $profileRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent() ?: '[]', true);
        $hair = (string)($payload['hairSkin'] ?? '');

        $allowed = ['bald_head.webp', 'dark_hair_head.webp', 'ginger_hair_head.webp', 'blond_hair_head.webp', 'brown_hair_head.webp' ];
        if (!in_array($hair, $allowed, true)) {
            return $this->json(['message' => 'Skin invalide.'], 400);
        }

        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['owner' => $user]);

        if (!$profile) {
            return $this->json(['message' => 'Profil joueur introuvable.'], 400);
        }

        $profile->setHairSkin($hair);
        $profile->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json([
            'ok' => true,
            'hairSkin' => $profile->getHairSkin(),
            'message' => 'Sauvegardé.',
        ]);
    }
}