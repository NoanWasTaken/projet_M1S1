<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    private string $secretKey;
    private string $publicKey;

    public function __construct(string $stripeSecretKey, string $stripePublicKey)
    {
        $this->secretKey = $stripeSecretKey;
        $this->publicKey = $stripePublicKey;
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Créer une session de paiement Stripe Checkout
     *
     * @param array $lineItems Les articles à payer (format Stripe)
     * @param string $successUrl URL de redirection après paiement réussi
     * @param string $cancelUrl URL de redirection si paiement annulé
     * @param array $metadata Métadonnées supplémentaires (ex: order_id)
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
                    'unit_amount' => (int)($item['price'] * 100), // Montant en centimes
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
}
