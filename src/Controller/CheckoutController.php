<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        private StripeService $stripeService
    ) {
    }

    #[Route('/create-session', name: 'app_checkout_create_session', methods: ['POST'])]
    public function createSession(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);

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

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                        'description' => substr($product->getDescription() ?? '', 0, 200),
                    ],
                    'unit_amount' => (int)($product->getPrice() * 100), // Montant en centimes
                ],
                'quantity' => $cartItem->getQuantity(),
            ];
        }

        try {
            $session = $this->stripeService->createCheckoutSession(
                $lineItems,
                $this->generateUrl('app_checkout_success', ['session_id' => '{CHECKOUT_SESSION_ID}'], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                [
                    'user_id' => $user->getId(),
                    'cart_id' => $cart->getId(),
                ]
            );

            return $this->redirect($session->url);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/success', name: 'app_checkout_success')]
    public function success(): Response
    {
        $this->cartService->clearCart($this->getUser());
        
        return $this->render('checkout/success.html.twig');
    }

    #[Route('/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('checkout/cancel.html.twig');
    }
}
