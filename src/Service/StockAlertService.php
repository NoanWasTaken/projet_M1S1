<?php

namespace App\Service;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpClient\HttpClient;


class StockAlertService
{
    private int $threshold;

    public function __construct(
        private readonly ProductsRepository $productsRepository,
    ) {
        $this->threshold = (int) ($_ENV['STOCK_ALERT_THRESHOLD'] ?? 10);
    }

    public function notifyOne(
        Products $product,
        int      $oldStock,
        ?int     $customThreshold = null
    ): void {
        $threshold = $customThreshold ?? $this->threshold;
        $this->post([
            'products' => [[
                'product_id'   => $product->getId(),
                'product_name' => $product->getName(),
                'brand'        => $product->getBrand(),
                'category'     => $product->getCategory(),
                'old_stock'    => $oldStock,
                'new_stock'    => $product->getStock(),
                'threshold'    => $threshold,
            ]],
            'threshold' => $threshold,
        ]);
    }

    public function checkAll(): array
    {
        $products       = $this->productsRepository->findAll();
        $alerted        = [];
        $productsPayload = [];

        foreach ($products as $product) {
            if ($product->getStock() < $this->threshold) {
                $productsPayload[] = [
                    'product_id'   => $product->getId(),
                    'product_name' => $product->getName(),
                    'brand'        => $product->getBrand(),
                    'category'     => $product->getCategory(),
                    'old_stock'    => $product->getStock(),
                    'new_stock'    => $product->getStock(),
                    'threshold'    => $this->threshold,
                ];
                $alerted[] = [
                    'id'    => $product->getId(),
                    'name'  => $product->getName(),
                    'stock' => $product->getStock(),
                ];
            }
        }

        if (!empty($productsPayload)) {
            $this->post([
                'products'  => $productsPayload,
                'threshold' => $this->threshold,
            ]);
        }

        return [
            'alerts'    => count($alerted),
            'threshold' => $this->threshold,
            'products'  => $alerted,
        ];
    }

    private function post(array $payload): void
    {
        $webhookUrl = $_ENV['N8N_WEBHOOK_URL'] ?? null;

        if (empty($webhookUrl)) {
            return;
        }

        try {
            $client = HttpClient::create();
            $client->request('POST', $webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Api-Key'    => $_ENV['N8N_API_KEY'] ?? '',
                ],
                'json'    => $payload,
                'timeout' => 2,
            ]);
        } catch (\Throwable) {
        }
    }
}
