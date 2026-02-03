<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    /**
     * Trouver tous les produits disponibles
     */
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

    /**
     * Trouver les produits par catégorie
     */
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

    /**
     * Rechercher des produits par nom ou description
     */
    public function searchProducts(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :query OR p.description LIKE :query')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.stock > 0')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('available', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir les catégories disponibles
     */
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
}
