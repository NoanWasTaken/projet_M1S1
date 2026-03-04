<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeService
{
    private string $secretKey;
    private string $publicKey;
    private ?string $webhookSecret;

    public function __construct(
        string $stripeSecretKey, 
        string $stripePublicKey,
        ?string $stripeWebhookSecret = null
    ) {
        $this->secretKey = $stripeSecretKey;
        $this->publicKey = $stripePublicKey;
        $this->webhookSecret = $stripeWebhookSecret;
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Créer une session de paiement Stripe Checkout
     *
     * @param array $lineItems Les articles à payer (format Stripe)
     * @param string $successUrl URL de redirection après paiement réussi
     * @param string $cancelUrl URL de redirection si paiement annulé
     * @param array $metadata Métadonnées supplémentaires 
     * @return Session
     * @throws ApiErrorException
     */
    public function createCheckoutSession(
        array $lineItems,
        string $successUrl,
        string $cancelUrl,
        array $metadata = []
    ): Session {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Récupérer une session de paiement
     *
     * @param string $sessionId
     * @return Session
     * @throws ApiErrorException
     */
    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Formater les articles du panier pour Stripe
     *
     * @param array $cartItems Les articles du panier
     * @return array
     */
    public function formatCartItemsForStripe(array $cartItems): array
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                        'images' => $item['images'] ?? [],
                    ],
                    'unit_amount' => (int)($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        return $lineItems;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Construire et vérifier un événement webhook Stripe
     * @param string 
     * @param string 
     * @return Event
     * @throws SignatureVerificationException 
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        if (!$this->webhookSecret) {
            throw new \RuntimeException('Webhook secret not configured');
        }

        return Webhook::constructEvent(
            $payload,
            $signature,
            $this->webhookSecret
        );
    }
}
