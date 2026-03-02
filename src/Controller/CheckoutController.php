<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
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
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
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
        $user = $this->getUser();
        $cart = $this->cartService->getOrCreateCart($user);

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

            $order->setTotal($total);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

        $this->cartService->clearCart($user);

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
