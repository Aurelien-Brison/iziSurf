<?php

namespace App\Repository;

use App\Entity\ResponseStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ResponseStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponseStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponseStatus[]    findAll()
 * @method ResponseStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponseStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponseStatus::class);
    }

    // /**
    //  * @return ResponseStatus[] Returns an array of ResponseStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResponseStatus
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
