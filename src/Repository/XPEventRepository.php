<?php

namespace App\Repository;

use App\Entity\XPEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XPEvent>
 */
class XPEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XPEvent::class);
    }

    //    /**
    //     * @return XPEvent[] Returns an array of XPEvent objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('x')
    //            ->andWhere('x.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('x.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?XPEvent
    //    {
    //        return $this->createQueryBuilder('x')
    //            ->andWhere('x.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
