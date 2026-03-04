<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/create-session', name: 'app_checkout_create_session', methods: ['POST'])]
    public function createSession(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        $promoCode = $request->getSession()->get('applied_promo_code');
        $totalWithPromo = null;
        $discountMultiplier = 1.0;
        
        // Calculer le multiplicateur de réduction si un code promo est appliqué
        if ($promoCode) {
            $totalWithPromo = $cart->getTotalWithPromo($promoCode, $this->entityManager, $user);
            $originalTotal = $cart->getTotal();
            if ($originalTotal > 0 && $totalWithPromo < $originalTotal) {
                $discountMultiplier = $totalWithPromo / $originalTotal;
            }
        }

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        $lineItems = [];
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();
            if ($cartItem->getQuantity() > $product->getStock()) {
                $this->addFlash('error', "Stock insuffisant pour {$product->getName()}");
                return $this->redirectToRoute('app_cart');
            }
            
            // Appliquer la réduction proportionnelle sur chaque produit
            $unitPrice = $product->getPrice() * $discountMultiplier;
            
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName() . ($promoCode ? " (promo: {$promoCode})" : ''),
                        'description' => substr($product->getDescription() ?? '', 0, 200),
                    ],
                    'unit_amount' => (int)($unitPrice * 100),
                ],
                'quantity' => $cartItem->getQuantity(),
            ];
        }
        
        try {
            $stripeSession = $this->stripeService->createCheckoutSession(
                $lineItems,
                $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                [
                    'user_id' => $user->getId(),
                    'cart_id' => $cart->getId(),
                    'promo_code' => $promoCode ?? '',
                ]
            );
            return $this->redirect($stripeSession->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);
        
        // Récupérer le code promo de la session avant de le supprimer
        $promoCode = $request->getSession()->get('applied_promo_code');

        if (!$cart->getItems()->isEmpty()) {
            foreach ($cart->getItems() as $cartItem) {
                $product = $cartItem->getProduct();
                if ($cartItem->getQuantity() > $product->getStock()) {
                    $this->cartService->clearCart($user);
                    $this->addFlash('error', sprintf(
                        'Stock insuffisant pour "%s" (disponible : %d). Votre panier a été vidé.',
                        $product->getName(),
                        $product->getStock()
                    ));
                    return $this->redirectToRoute('app_home');
                }
            }

            $order = new Order();
            $order->setUser($user);
            $order->setStatus(OrderStatus::VALIDATED);

            $total = 0.0;
            foreach ($cart->getItems() as $cartItem) {
                $product = $cartItem->getProduct();
                $qty     = $cartItem->getQuantity();
                $product->setStock($product->getStock() - $qty);

                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($qty);
                $orderItem->setUnitPrice((float) $product->getPrice());
                $order->addItem($orderItem);
                $total += $orderItem->getSubtotal();
            }
            
            // Appliquer le code promo au total de la commande si présent
            if ($promoCode) {
                $totalWithPromo = $cart->getTotalWithPromo($promoCode, $this->entityManager, $user);
                $order->setTotal($totalWithPromo);
            } else {
                $order->setTotal($total);
            }
            
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

        $this->cartService->clearCart($user);
        
        // Supprimer le code promo de la session après le paiement réussi
        $request->getSession()->remove('applied_promo_code');

        return $this->render('checkout/success.html.twig', [
            'showIntroDialogue' => false,
        ]);
    }

    #[Route('/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('checkout/cancel.html.twig', [
            'showIntroDialogue' => false,
        ]);
    }
}
