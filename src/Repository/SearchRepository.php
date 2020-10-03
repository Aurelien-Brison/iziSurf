<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Search;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Search|null find($id, $lockMode = null, $lockVersion = null)
 * @method Search|null findOneBy(array $criteria, array $orderBy = null)
 * @method Search[]    findAll()
 * @method Search[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Search::class);
    }

    public function rideWanted($ride) 
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.isNotifiedWhenResult = 1')
            ->andWhere('s.cityDeparture = :cityDeparture' )
            ->setParameter('cityDeparture', $ride->getCityDeparture())
            ->andWhere('s.departureDate = :departureDate' )
            ->setParameter('departureDate', $ride->getDepartureDate())
            ->andWhere('s.returnDate = :returnDate' )
            ->setParameter('returnDate', $ride->getReturnDate())
            ->andWhere('s.availableSeat <= :availableSeat')
            ->setParameter('availableSeat', $ride->getAvailableSeat())
            ->andWhere('s.boardMax <= :boardMax')
            ->setParameter('boardMax', $ride->getBoardMax())
            ->andWhere('s.boardSizeMax <= :boardSizeMax')
            ->setParameter('boardSizeMax', $ride->getBoardSizeMax())
            ->andWhere('s.boardSizeMax <= :boardSizeMax')
            ->setParameter('boardSizeMax', $ride->getBoardSizeMax())
            ->andWhere('s.isSameGender = :isSameGender')
            ->setParameter('isSameGender', $ride->getIsSameGender())
            ->innerJoin('s.spot', 'spot')
            ->andWhere('spot.name = :spotName')
            ->setParameter('spotName', $ride->getSpot()->getName())   
        ;
        
        return  $query->getQuery()->getResult();
    }

    // /**
    //  * @return Search[] Returns an array of Search objects
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
    public function findOneBySomeField($value): ?Search
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findBySearchByuserId(User $user)
    {
        $qb = $this->createQueryBuilder('s')
            ->Where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(10);

            return $qb->getQuery()->getResult()
        ;
    }
}
