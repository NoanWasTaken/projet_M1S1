<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByProduct(int $productId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.author', 'u')
            ->addSelect('u')
            ->where('r.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
