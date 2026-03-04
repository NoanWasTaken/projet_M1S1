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
    #[Route('/import/{token}', name: 'app_cart_import_shared', methods: ['POST'])]
    public function importShared(string $token): Response
    {
        $user = $this->getUser();
        $sharedCart = $this->cartService->getCartByShareToken($token);
        if (!$sharedCart || $sharedCart->getItems()->count() === 0) {
            $this->addFlash('error', 'Impossible d\'importer ce panier.');
            return $this->redirectToRoute('app_cart');
        }
        $userCart = $this->cartService->getOrCreateCart($user);
        $this->cartService->importCartItems($sharedCart, $userCart);
        $this->addFlash('success', 'Panier importé avec succès !');
        return $this->redirectToRoute('app_cart');
    }
    #[Route('/share', name: 'app_cart_share', methods: ['GET'])]
    public function share(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        if ($cart->getItems()->count() === 0) {
            $this->addFlash('error', 'Impossible de partager un panier vide.');
            return $this->redirectToRoute('app_cart');
        }
        $this->cartService->ensureShareToken($cart);
        $shareUrl = $this->generateUrl('app_cart_shared_view', [
            'token' => $cart->getShareToken()
        ], 0);
        return $this->render('cart/share.html.twig', [
            'shareUrl' => $shareUrl,
            'cart' => $cart,
            'showIntroDialogue' => false,
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
            'showIntroDialogue' => false,
        ]);
    }
    public function __construct(
        private CartService $cartService,
        private ProductsRepository $productRepository
    ) {}

    #[Route('', name: 'app_cart')]
    public function index(Request $request, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        $this->cartService->ensureShareToken($cart);
        $savedCartForm = $this->createForm(\App\Form\SavedCartType::class);
        
        // Calculer le total avec promo si applicable
        $appliedPromoCode = $request->getSession()->get('applied_promo_code');
        $totalWithPromo = null;
        if ($appliedPromoCode) {
            $totalWithPromo = $cart->getTotalWithPromo($appliedPromoCode, $em, $user);
        }
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'savedCartForm' => $savedCartForm->createView(),
            'showIntroDialogue' => false,
            'totalWithPromo' => $totalWithPromo,
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

    #[Route('/apply-promo', name: 'app_cart_apply_promo', methods: ['POST'])]
    public function applyPromo(Request $request, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $code = trim($request->request->get('promo_code', ''));
        if (!$code) {
            $this->addFlash('error', 'Veuillez entrer un code promo.');
            return $this->redirectToRoute('app_cart');
        }
        $promoRepo = $em->getRepository(\App\Entity\PromoCode::class);
        $promo = $promoRepo->findOneBy(['code' => $code]);
        if (!$promo || !$promo->isActive()) {
            $this->addFlash('error', 'Code promo invalide ou expiré.');
            return $this->redirectToRoute('app_cart');
        }
        if (!$user->getPromoCodes()->contains($promo)) {
            $this->addFlash('error', 'Ce code promo n\'a pas été débloqué sur votre compte.');
            return $this->redirectToRoute('app_cart');
        }
        $request->getSession()->set('applied_promo_code', $promo->getCode());
        $this->addFlash('success', 'Code promo appliqué !');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove-promo', name: 'app_cart_remove_promo', methods: ['POST'])]
    public function removePromo(Request $request): Response
    {
        $request->getSession()->remove('applied_promo_code');
        $this->addFlash('success', 'Code promo retiré.');
        return $this->redirectToRoute('app_cart');
    }
}
