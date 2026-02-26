<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    #[Route('/share', name: 'app_cart_share', methods: ['GET'])]
    public function share(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        $this->cartService->ensureShareToken($cart);
        $shareUrl = $this->generateUrl('app_cart_shared_view', [
            'token' => $cart->getShareToken()
        ], 0);
        return $this->render('cart/share.html.twig', [
            'shareUrl' => $shareUrl,
            'cart' => $cart,
        ]);
    }

    #[Route('/shared/{token}', name: 'app_cart_shared_view', methods: ['GET'])]
    public function viewShared(string $token): Response
    {
        $cart = $this->cartService->getCartByShareToken($token);
        if (!$cart) {
            throw $this->createNotFoundException('Panier partagé introuvable');
        }
        return $this->render('cart/shared_view.html.twig', [
            'cart' => $cart,
        ]);
    }
    public function __construct(
        private CartService $cartService,
        private ProductsRepository $productRepository
    ) {}

    #[Route('', name: 'app_cart')]
    public function index(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        if (!$product->isAvailable() || $product->getStock() <= 0) {
            $this->addFlash('error', 'Ce produit n\'est pas disponible');
            return $this->redirectToRoute('app_catalogue');
        }
        $quantity = (int) $request->request->get('quantity', 1);
        if ($quantity > $product->getStock()) {
            $this->addFlash('error', 'Stock insuffisant');
            return $this->redirectToRoute('app_product_detail', ['id' => $id]);
        }
        $this->cartService->addProduct($this->getUser(), $product, $quantity);
        $this->addFlash('success', '✅ Produit ajouté au panier !');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $this->cartService->updateQuantity($this->getUser(), $id, $quantity);
        $this->addFlash('success', 'Quantité mise à jour');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id): Response
    {
        $this->cartService->removeProduct($this->getUser(), $id);
        $this->addFlash('success', 'Produit retiré du panier');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->cartService->clearCart($this->getUser());
        $this->addFlash('success', 'Panier vidé');
        return $this->redirectToRoute('app_cart');
    }
}
