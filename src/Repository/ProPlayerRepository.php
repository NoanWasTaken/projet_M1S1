<?php
namespace App\Repository;

use App\Entity\ProPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProPlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProPlayer::class);
    }

    /**
     * @return ProPlayer[]
     */
    public function findByGame(string $game): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getResult();
    }
}
