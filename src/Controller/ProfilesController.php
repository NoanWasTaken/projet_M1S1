<?php
namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profiles')]
#[IsGranted('ROLE_USER')]
class ProfilesController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private ProductsRepository $productsRepository,
        private \App\Repository\ProPlayerRepository $proPlayerRepository
    ) {}

    #[Route('/add-to-cart', name: 'app_profiles_add_to_cart', methods: ['POST'])]
    public function addToCart(Request $request): Response
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        if (!$name) {
            return $this->json(['error' => 'Nom du produit manquant'], 400);
        }
        $product = $this->productsRepository->findOneBy(['name' => $name]);
        if (!$product) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }
        $this->cartService->addProduct($user, $product, 1);
        return $this->json(['success' => true]);
    }

    #[Route('', name: 'app_profiles_index', methods: ['GET'])]
    public function index(): Response
    {
        $cart = null;
        if ($this->getUser()) {
            $cart = $this->cartService->getOrCreateCart($this->getUser());
        }
        $players = $this->proPlayerRepository->findAll();
        return $this->render('profiles.html.twig', [
            'cart' => $cart,
            'players' => $players,
            'showIntroDialogue' => false,
        ]);
    }
}
