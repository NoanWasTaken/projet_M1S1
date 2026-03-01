<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private \App\Service\CartService $cartService) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $cart = null;
        if ($this->getUser()) {
            $cart = $this->cartService->getOrCreateCart($this->getUser());
        }
        return $this->render('home/index.html.twig', [
            'cart' => $cart,
        ]);
    }
}
