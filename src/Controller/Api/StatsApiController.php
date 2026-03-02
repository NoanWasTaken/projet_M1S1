<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats', name: 'api_stats_')]
class StatsApiController extends AbstractController
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'generated_at'            => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'revenue'                 => $this->getRevenue(),
            'orders_by_status'        => $this->getOrdersByStatus(),
            'new_orders_last_7_days'  => $this->getNewOrdersLastNDays(7),
            'total_users'             => $this->getTotalUsers(),
            'low_stock_products'      => $this->getLowStockProducts(),
        ]);
    }

    private function getRevenue(): array
    {
        $result = $this->em->createQuery(
            "SELECT COALESCE(SUM(o.total), 0) AS total
             FROM App\Entity\Order o
             WHERE o.status NOT IN ('pending', 'cancelled')"
        )->getSingleScalarResult();

        return [
            'total'    => round((float) $result, 2),
            'currency' => 'EUR',
        ];
    }

    private function getOrdersByStatus(): array
    {
        $rows = $this->em->createQuery(
            'SELECT o.status AS status, COUNT(o.id) AS cnt
             FROM App\Entity\Order o
             GROUP BY o.status'
        )->getResult();

        $byStatus = [];
        foreach ($rows as $row) {
            $key            = $row['status'] instanceof \BackedEnum
                ? $row['status']->value
                : (string) $row['status'];
            $byStatus[$key] = (int) $row['cnt'];
        }

        return $byStatus;
    }

    private function getNewOrdersLastNDays(int $days): int
    {
        $since = new \DateTimeImmutable("-$days days");

        return (int) $this->em->createQuery(
            'SELECT COUNT(o.id)
             FROM App\Entity\Order o
             WHERE o.createdAt >= :since'
        )->setParameter('since', $since)->getSingleScalarResult();
    }

    private function getTotalUsers(): int
    {
        return (int) $this->em->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u'
        )->getSingleScalarResult();
    }

    private function getLowStockProducts(): array
    {
        $products = $this->em->createQuery(
            'SELECT p
             FROM App\Entity\Products p
             WHERE p.stock < :threshold
             ORDER BY p.stock ASC'
        )->setParameter('threshold', self::LOW_STOCK_THRESHOLD)->getResult();

        return array_map(fn ($p) => [
            'id'       => $p->getId(),
            'name'     => $p->getName(),
            'brand'    => $p->getBrand(),
            'category' => $p->getCategory(),
            'stock'    => $p->getStock(),
            'price'    => $p->getPrice(),
        ], $products);
    }
    private function isAuthorized(Request $request): bool
    {
        $expectedKey = $_ENV['N8N_API_KEY'] ?? null;

        if (empty($expectedKey)) {
            return true;
        }

        return $request->headers->get('X-Api-Key') === $expectedKey;
    }
}
