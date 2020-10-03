<?php

namespace App\Repository;

use App\Entity\SurfInfoUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SurfInfoUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurfInfoUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurfInfoUser[]    findAll()
 * @method SurfInfoUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurfInfoUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurfInfoUser::class);
    }

    // /**
    //  * @return SurfInfoUser[] Returns an array of SurfInfoUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SurfInfoUser
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
