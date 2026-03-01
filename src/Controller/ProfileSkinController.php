<?php

namespace App\Controller;

use App\Entity\PlayerProfile;
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
        $body = (string)($payload['bodySkin'] ?? '');

        $allowedHair = ['bald_head.webp', 'dark_hair_head.webp', 'ginger_hair_head.webp', 'blond_hair_head.webp', 'brown_hair_head.webp' ];
        $allowedBody = ['normal_body.webp', 'large_body.webp', 'muscle_body.webp', 'rounded_body.webp'];

        if ($hair && !in_array($hair, $allowedHair, true)) {
            return $this->json(['message' => 'Cheveux invalides.'], 400);
        }

        if ($body && !in_array($body, $allowedBody, true)) {
            return $this->json(['message' => 'Corps invalide.'], 400);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['owner' => $user]);

        if (!$profile) {
            $profile = new PlayerProfile();
            $profile->setOwner($user);
            $profile->setCreatedAt(new \DateTimeImmutable());
            $em->persist($profile);
        }

        if ($hair) {
            $profile->setHairSkin($hair);
        }
        if ($body) {
            $profile->setBodySkin($body);
        }
        
        $profile->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json([
            'ok' => true,
            'hairSkin' => $profile->getHairSkin(),
            'bodySkin' => $profile->getBodySkin(),
            'message' => 'Sauvegardé.',
        ]);
    }
}