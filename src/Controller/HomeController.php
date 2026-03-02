<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
class HomeController extends AbstractController
{
    public function __construct(
        private \App\Service\CartService $cartService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $cart = null;
        $showIntroDialogue = false;
        $user = $this->getUser();
        if ($user) {
            $cart = $this->cartService->getOrCreateCart($user);
            $introRepo = $this->entityManager->getRepository(\App\Entity\IntroProfileAnswer::class);
            $showIntroDialogue = !$introRepo->findByUser($user);
        }
        return $this->render('home/index.html.twig', [
            'cart' => $cart,
            'showIntroDialogue' => $showIntroDialogue,
        ]);
    }
}
