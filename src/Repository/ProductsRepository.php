<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }


    public function findAvailableProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('available', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('category', $category)
            ->setParameter('available', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function searchProducts(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE LOWER(:query) OR LOWER(p.description) LIKE LOWER(:query) OR LOWER(p.brand) LIKE LOWER(:query)')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('available', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function getAvailableCategories(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('DISTINCT p.category')
            ->where('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('available', true)
            ->getQuery()
            ->getResult();

        return array_column($result, 'category');
    }

    // Meilleurs produits par game types — tri : matchCount DESC, rating DESC
    // Sans filtre catégorie : 1 produit par catégorie. Avec : tous les résultats.
    public function findBestByGameTypes(array $gameTypeNames, ?float $maxPrice = null, ?float $minPrice = null, ?string $category = null): array
    {
        if (empty($gameTypeNames)) {
            return [];
        }

        $qb = $this->createQueryBuilder('p')
            ->select('p', 'COUNT(gt.id) as HIDDEN matchCount')
            ->innerJoin('p.game_types', 'gt')
            ->where('gt.type IN (:types)')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('types', $gameTypeNames)
            ->setParameter('available', true)
            ->groupBy('p.id')
            ->orderBy('matchCount', 'DESC')
            ->addOrderBy('p.rating', 'DESC');

        if ($maxPrice !== null) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        if ($minPrice !== null) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($category !== null) {
            $qb->andWhere('LOWER(p.category) = LOWER(:category)')
               ->setParameter('category', $category);
        }

        $products = $qb->getQuery()->getResult();

        // Avec catégorie : tous les résultats. Sans : 1 par catégorie.
        if ($category !== null) {
            return $products;
        }

        $bestByCategory = [];
        foreach ($products as $product) {
            $cat = $product->getCategory() ?? 'Autre';
            if (!isset($bestByCategory[$cat])) {
                $bestByCategory[$cat] = $product;
            }
        }

        return array_values($bestByCategory);
    }

    public function findTopByCategory(string $category, ?float $maxPrice = null, ?float $minPrice = null, int $limit = 4): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.category) = LOWER(:cat)')
            ->setParameter('cat', $category)
            ->andWhere('p.isAvailable = true')
            ->andWhere('p.stock > 0')
            ->orderBy('p.rating', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($maxPrice !== null) {
            $qb->andWhere('p.price <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }

        if ($minPrice !== null) {
            $qb->andWhere('p.price >= :minPrice')->setParameter('minPrice', $minPrice);
        }

        return $qb->getQuery()->getResult();
    }

    public function updateAverageRating(Products $product): void
    {
        $avg = $this->getEntityManager()
            ->createQuery('SELECT AVG(r.rating) FROM App\Entity\Review r WHERE r.product = :product')
            ->setParameter('product', $product)
            ->getSingleScalarResult();

        $product->setRating($avg !== null ? number_format(round((float) $avg, 1), 1, '.', '') : null);
    }

    // Correspondance partielle de noms — pour compare_products
    public function findByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $qb = $this->createQueryBuilder('p')
            ->where('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('available', true);

        $orConditions = [];
        foreach ($names as $i => $name) {
            $orConditions[] = "LOWER(p.name) LIKE LOWER(:name$i)";
            $qb->setParameter("name$i", '%' . $name . '%');
        }
        $qb->andWhere(implode(' OR ', $orConditions));

        return $qb->getQuery()->getResult();
    }
}