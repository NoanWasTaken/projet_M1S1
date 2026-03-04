<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Cart;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }


    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        if (!$signature) {
            $this->logger->error('Webhook Stripe: Signature manquante');
            return new Response('Signature missing', Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (\Exception $e) {
            $this->logger->error('Webhook Stripe: Erreur de vérification de signature', [
                'error' => $e->getMessage()
            ]);
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;

                case 'checkout.session.expired':
                    $this->logger->info('Session de paiement expirée', [
                        'session_id' => $event->data->object->id
                    ]);
                    break;

                default:
                    $this->logger->info('Événement webhook non géré', [
                        'type' => $event->type
                    ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du webhook', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new Response('Webhook processing error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('Webhook handled', Response::HTTP_OK);
    }


    private function handleCheckoutSessionCompleted($session): void
    {
        $this->logger->info('Paiement réussi via webhook', [
            'session_id' => $session->id,
            'metadata' => $session->metadata
        ]);

        $userId = $session->metadata['user_id'] ?? null;
        $cartId = $session->metadata['cart_id'] ?? null;
        $promoCode = $session->metadata['promo_code'] ?? null;

        if (!$userId || !$cartId) {
            $this->logger->error('Métadonnées manquantes dans la session Stripe', [
                'session_id' => $session->id
            ]);
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $cart = $this->entityManager->getRepository(Cart::class)->find($cartId);

        if (!$user || !$cart) {
            $this->logger->error('Utilisateur ou panier introuvable', [
                'user_id' => $userId,
                'cart_id' => $cartId
            ]);
            return;
        }

        $existingOrder = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['user' => $user, 'stripeSessionId' => $session->id]);

        if ($existingOrder) {
            $this->logger->info('Commande déjà créée pour cette session', [
                'order_id' => $existingOrder->getId(),
                'session_id' => $session->id
            ]);
            return;
        }

        if ($cart->getItems()->isEmpty()) {
            $this->logger->warning('Panier vide lors de la création de commande', [
                'cart_id' => $cartId
            ]);
            return;
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus(OrderStatus::VALIDATED);
        $order->setStripeSessionId($session->id);

        $total = 0.0;
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $qty = $cartItem->getQuantity();

            if ($qty > $product->getStock()) {
                $this->logger->error('Stock insuffisant lors du webhook', [
                    'product_id' => $product->getId(),
                    'requested' => $qty,
                    'available' => $product->getStock()
                ]);
                continue;
            }

            $product->setStock($product->getStock() - $qty);

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($qty);
            $orderItem->setUnitPrice((float) $product->getPrice());
            $order->addItem($orderItem);
            $total += $orderItem->getSubtotal();
        }

        if ($promoCode) {
            $totalWithPromo = $cart->getTotalWithPromo($promoCode, $this->entityManager, $user);
            $order->setTotal($totalWithPromo);
        } else {
            $order->setTotal($total);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->logger->info('Commande créée avec succès via webhook', [
            'order_id' => $order->getId(),
            'session_id' => $session->id,
            'total' => $order->getTotal()
        ]);
    }
}
