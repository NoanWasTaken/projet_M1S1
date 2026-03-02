<?php
namespace App\Repository;

use App\Entity\IntroProfileAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IntroProfileAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntroProfileAnswer::class);
    }
    public function findByUser($user)
    {
        return $this->findOneBy(['user' => $user]);
    }
}
