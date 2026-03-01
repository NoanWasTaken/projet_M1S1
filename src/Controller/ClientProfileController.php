<?php

namespace App\Controller;

use App\Repository\PlayerProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ClientProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_client_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(PlayerProfileRepository $profileRepo): Response
    {
        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['owner' => $user]); 

        $xpTotal = $profile?->getXpTotal() ?? 0;
        $level   = $profile?->getLevel() ?? 1;

        $bodySkin = $profile?->getBodySkin() ?? 'normal_body.webp';
        $hairSkin = $profile?->getHairSkin() ?? 'bald_head.webp';

        $xpInLevel = $xpTotal % 1000;
        $xpPercent = (int) round(($xpInLevel / 1000) * 100);

        return $this->render('profile/profileClient.html.twig', [
            'profile'   => $profile,
            'level'     => $level,
            'xpTotal'   => $xpTotal,
            'xpInLevel' => $xpInLevel,
            'xpPercent' => $xpPercent,
            'hairSkin' => $hairSkin,
            'bodySkin' => $bodySkin,
        ]);
    }
}