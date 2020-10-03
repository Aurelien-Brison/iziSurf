<?php

namespace App\Repository;

use App\Entity\Fit;
use App\Entity\Ride;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Fit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fit[]    findAll()
 * @method Fit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fit::class);
    }

    public function findRideByFavoritesTotal(User $user)
    {
        $today = new DateTime("today");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.isFavorite = 1')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getScalarResult();
    }

    public function findRideByFavorites(User $user, Request $request)
    {
        $today = new DateTime("today");
        $start = $request->query->get("startFavourites");
        $limit = $request->query->get("limitFavourites");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.isFavorite = 1')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getResult();
    }

    public function findRideByResponseStatusAcceptedTotal(User $user)
    {
        $today = new DateTime("today");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.status = 2')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getScalarResult();
    }

    public function findRideByResponseStatusAccepted(User $user, Request $request)
    {
        $today = new DateTime("today");
        $start = $request->query->get("startAccepted");
        $limit = $request->query->get("limitAccepted");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.status = 2')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getResult();
    }

    public function findRideByResponseStatusWaitingTotal(User $user)
    {
        $today = new DateTime("today");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.status = 1')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getScalarResult();
    }


    public function findRideByResponseStatusWaiting(User $user, Request $request)
    {
        $today = new DateTime("today");
        $start = $request->query->get("startPending");
        $limit = $request->query->get("limitPending");

        $qb = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.status = 1')
            ->innerJoin('f.ride', 'r')
            ->andWhere('r.departureDate >= :today' )
            ->setParameter('today', $today)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('r.departureDate', 'ASC');
        
        return  $qb->getQuery()->getResult();
    }

    public function fetchFitByRideIdAndUserId(Ride $ride, User $user)
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.ride = :ride')
            ->setParameter('ride', $ride)
            ->andWhere('f.user = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function fetchFitByRideIdAndUserIdWithoutEntityUser(Ride $ride, $user)
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.ride = :ride')
            ->setParameter('ride', $ride)
            ->andWhere('f.user = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }



    // public function findFitByUserIdDQL(User $user)
    // {
    //          $query = $this->getEntityManager()
    //         ->createQuery(
    //             'SELECT fit FROM App\Entity\Fit AS fit
    //             WHERE fit.user = :user
    //             AND fit.isFavorite = 1')
    //             ->setParameter('user', $user);
        

    //     return  $query->getResult();

     
    // }

    // /**
    //  * @return Match[] Returns an array of Match objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Match
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findUserByWaitRide(Ride $ride)
    {
        $qb = $this->createQueryBuilder('f')
        ->where('f.ride = :ride')
        ->andWhere('f.status = 1')
        ->setParameter('ride', $ride);

        return $qb->getQuery()->getResult();
    }

    public function findUserByAcceptRide(Ride $ride)
    {
        $qb = $this->createQueryBuilder('f')
        ->where('f.ride = :ride')
        ->andWhere('f.status = 2')
        ->setParameter('ride', $ride);

        return $qb->getQuery()->getResult();
    }

    public function findUserByRefuseRide(Ride $ride)
    {
        $qb = $this->createQueryBuilder('f')
        ->where('f.ride = :ride')
        ->andWhere('f.status = 3')
        ->setParameter('ride', $ride);

        return $qb->getQuery()->getResult();
    }
}
