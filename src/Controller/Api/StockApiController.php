<?php

namespace App\Controller\Api;

use App\Repository\ProductsRepository;
use App\Service\StockAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stock', name: 'api_stock_')]
class StockApiController extends AbstractController
{
    public function __construct(
        private readonly ProductsRepository    $productsRepository,
        private readonly EntityManagerInterface $em,
        private readonly StockAlertService      $stockAlert,
    ) {}

    private function isAuthorized(Request $request): bool
    {
        $expectedKey = $_ENV['N8N_API_KEY'] ?? null;

        if (empty($expectedKey)) {
            return true;
        }

        return $request->headers->get('X-Api-Key') === $expectedKey;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $products = $this->productsRepository->findAll();

        $data = array_map(fn ($p) => [
            'id'       => $p->getId(),
            'name'     => $p->getName(),
            'brand'    => $p->getBrand(),
            'category' => $p->getCategory(),
            'stock'    => $p->getStock(),
            'price'    => $p->getPrice(),
        ], $products);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $product = $this->productsRepository->find($id);

        if (!$product) {
            return $this->json(['error' => "Produit #$id introuvable."], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id'       => $product->getId(),
            'name'     => $product->getName(),
            'brand'    => $product->getBrand(),
            'category' => $product->getCategory(),
            'stock'    => $product->getStock(),
            'price'    => $product->getPrice(),
        ]);
    }


    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $q = trim((string) $request->query->get('q', ''));

        if ($q === '') {
            return $this->json(['error' => 'Paramètre q manquant.'], Response::HTTP_BAD_REQUEST);
        }

        $products = $this->productsRepository->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE LOWER(:q)')
            ->orWhere('LOWER(p.brand) LIKE LOWER(:q)')
            ->setParameter('q', '%' . $q . '%')
            ->getQuery()
            ->getResult();

        if (empty($products)) {
            return $this->json(['error' => "Aucun produit trouvé pour « $q »."], Response::HTTP_NOT_FOUND);
        }

        $data = array_map(fn ($p) => [
            'id'       => $p->getId(),
            'name'     => $p->getName(),
            'brand'    => $p->getBrand(),
            'category' => $p->getCategory(),
            'stock'    => $p->getStock(),
            'price'    => $p->getPrice(),
        ], $products);

        return $this->json($data);
    }


    #[Route('/{id}', name: 'update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $product = $this->productsRepository->find($id);

        if (!$product) {
            return $this->json(['error' => "Produit #$id introuvable."], Response::HTTP_NOT_FOUND);
        }

        $body     = json_decode($request->getContent(), true) ?? [];
        $oldStock = $product->getStock(); 

        if (isset($body['stock'])) {
            $newStock = (int) $body['stock'];
            if ($newStock < 0) {
                return $this->json(['error' => 'Le stock ne peut pas être négatif.'], Response::HTTP_BAD_REQUEST);
            }
            $product->setStock($newStock);
        } elseif (isset($body['delta'])) {
            $newStock = $product->getStock() + (int) $body['delta'];
            if ($newStock < 0) {
                return $this->json(['error' => 'Le stock résultant serait négatif.'], Response::HTTP_BAD_REQUEST);
            }
            $product->setStock($newStock);
        } else {
            return $this->json(['error' => 'Champ "stock" ou "delta" requis.'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();
        $this->stockAlert->notifyOne($product, $oldStock);

        return $this->json([
            'success' => true,
            'id'      => $product->getId(),
            'name'    => $product->getName(),
            'stock'   => $product->getStock(),
        ]);
    }
}
