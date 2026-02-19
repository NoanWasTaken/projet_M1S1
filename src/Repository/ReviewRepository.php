<?php

namespace App\Repository;

use App\Entity\Products;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }
    
    // returns all reviews for product, ordered DESC creation date
    public function findByProduct(Products $product): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.author', 'u')
            ->addSelect('u')
            ->where('r.product = :product')
            ->setParameter('product', $product)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
