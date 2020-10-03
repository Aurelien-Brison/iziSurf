<?php

namespace App\Repository;

use DateTime;
use App\Entity\Search;
use App\Entity\Ride;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Ride|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ride|null findOneBy(array $search, array $orderBy = null)
 * @method Ride[]    findAll()
 * @method Ride[]    findBy(array $search, array $orderBy = null, $limit = null, $offset = null)
 */
class RideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ride::class);
    }

    public function findUserAllRidesTotal(User $user) 
    {
        $today = new DateTime("today");

        $qb = $this->createQueryBuilder('r')
            ->where('r.driver = :user')
            ->setParameter('user', $user)
            ->andWhere('r.departureDate >= :today')
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'ASC');
    
        return $qb->getQuery()->getScalarResult();
    }

    public function findUserAllRides(User $user, Request $request) 
    {
        $today = new DateTime("today");
        $start = $request->query->get("startCreated");
        $limit = $request->query->get("limitCreated");

        $qb = $this->createQueryBuilder('r')
            ->where('r.driver = :user')
            ->setParameter('user', $user)
            ->andWhere('r.departureDate >= :today')
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'ASC')
            ->setFirstResult($start)
            ->setMaxResults($limit);
    
        return $qb->getQuery()->getResult();
    }

    public function findUserAllArchivedRidesTotal(User $user) 
    {
        $today = new DateTime("today");

        $qb = $this->createQueryBuilder('r')
            ->where('r.driver = :user')
            ->setParameter('user', $user)
            ->andWhere('r.departureDate < :today')
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'DESC');
    
        return $qb->getQuery()->getScalarResult();
    }
    
    public function findUserAllArchivedRides(User $user, Request $request) 
    {
        $today = new DateTime("today");
        $start = $request->query->get("startArchived");
        $limit = $request->query->get("limitArchived");

        $qb = $this->createQueryBuilder('r')
            ->where('r.driver = :user')
            ->setParameter('user', $user)
            ->andWhere('r.departureDate < :today')
            ->setParameter('today', $today)
            ->orderBy('r.departureDate', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);
    
        return $qb->getQuery()->getResult();
    }

    // Résultats jour voulu TOTAL
    public function dDayTotal($search)
    {
        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->addSelect('((ACOS(SIN(:cityLat * PI() / 180) * SIN(r.cityLatitude * PI() / 180) + COS(:cityLat * PI() / 180) * COS(r.cityLatitude * PI() / 180) * COS((:cityLng - r.cityLongitude) * PI() / 180)) * 180 / PI())) as HIDDEN cityDistance')
            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)
            ->setParameter('cityLat', $cityLatitude)
            ->setParameter('cityLng', $cityLongitude)
            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)
            ->andWhere('r.departureDate = :departureDate')
            ->setParameter('departureDate', $search->getDepartureDate())
            ->andWhere('r.returnDate = :returnDate')
            ->setParameter('returnDate', $search->getReturnDate())
            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())
            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())
            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())
            ;

        if ($search->getIsSameGender() == 0) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', [0,1]);
        }

        if ($search->getIsSameGender() == 3) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', [0,2]);
        }

        if ($search->getIsSameGender() == 1) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', 1);
        }

        if ($search->getIsSameGender() == 2) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', 2);
        }

        return $query
            ->getQuery()
            ->getScalarResult();
    }

    // Résultats jour voulu FILTRE DISTANCE VILLE DE DEPART
    public function dDayCityDeparture($search, $request)
    {
        $start = $request->query->get("startDDayCityDeparture");
        $limit = $request->query->get("limit");

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->addSelect('((ACOS(SIN(:cityLat * PI() / 180) * SIN(r.cityLatitude * PI() / 180) + COS(:cityLat * PI() / 180) * COS(r.cityLatitude * PI() / 180) * COS((:cityLng - r.cityLongitude) * PI() / 180)) * 180 / PI())) as HIDDEN cityDistance')
            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)
            ->setParameter('cityLat', $cityLatitude)
            ->setParameter('cityLng', $cityLongitude)
            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)
            ->andWhere('r.departureDate = :departureDate')
            ->setParameter('departureDate', $search->getDepartureDate())
            ->andWhere('r.returnDate = :returnDate')
            ->setParameter('returnDate', $search->getReturnDate())
            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())
            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())
            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())
            ->orderBy('cityDistance')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ;

        if ($search->getIsSameGender() == 0) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', [0,1]);
        }

        if ($search->getIsSameGender() == 3) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', [0,2]);
        }

        if ($search->getIsSameGender() == 1) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', 1);
        }

        if ($search->getIsSameGender() == 2) {
            $query = $query
            ->andWhere('r.isSameGender IN (:isSameGender)')
            ->setParameter('isSameGender', 2);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    // Résultats jour voulu FILTRE DISTANCE SPOT ARRIVEE
    public function dDaySpotArrival($search, $request)
    {
        $start = $request->query->get("startDDaySpotArrival");
        $limit = $request->query->get("limit");

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')
            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)
            ->innerJoin('r.spot', 'spot')
            ->addSelect('((ACOS(SIN(:spotLat * PI() / 180) * SIN(spot.latitude * PI() / 180) + COS(:spotLat * PI() / 180) * COS(spot.latitude * PI() / 180) * COS((:spotLng - spot.longitude) * PI() / 180)) * 180 / PI())) as HIDDEN spotDistance')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)
            ->setParameter('spotLat', $spotLatitude)
            ->setParameter('spotLng', $spotLongitude)
            ->andWhere('r.departureDate = :departureDate')
            ->setParameter('departureDate', $search->getDepartureDate())
            ->andWhere('r.returnDate = :returnDate')
            ->setParameter('returnDate', $search->getReturnDate())
            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())
            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())
            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('spotDistance')
            ;

            if ($search->getIsSameGender() == 0) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', [0,1]);
            }
    
            if ($search->getIsSameGender() == 3) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', [0,2]);
            }
    
            if ($search->getIsSameGender() == 1) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', 1);
            }
    
            if ($search->getIsSameGender() == 2) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', 2);
            }

        return $query
            ->getQuery()
            ->getResult();
    }

    // Résultats jour voulu FILTRE PRIX
    public function dDayPrice($search, $request)
    {
        $start = $request->query->get("startDDayPrice");
        $limit = $request->query->get("limit");

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')
            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)
            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)
            ->andWhere('r.departureDate = :departureDate')
            ->setParameter('departureDate', $search->getDepartureDate())
            ->andWhere('r.returnDate = :returnDate')
            ->setParameter('returnDate', $search->getReturnDate())
            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())
            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())
            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('r.price');

            if ($search->getIsSameGender() == 0) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', [0,1]);
            }
    
            if ($search->getIsSameGender() == 3) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', [0,2]);
            }
    
            if ($search->getIsSameGender() == 1) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', 1);
            }
    
            if ($search->getIsSameGender() == 2) {
                $query = $query
                ->andWhere('r.isSameGender IN (:isSameGender)')
                ->setParameter('isSameGender', 2);
            }

        return $query
            ->getQuery()
            ->getResult();
    }

    /*************************************** OTHER DAYS ***************************************/

    // Proposition de trajet si aucun résultat TOTAL
    public function otherDayTotal($search)
    {
        $currentDepartureDate = clone $search->getDepartureDate()->modify("-10 days");
        $departureDateMoreFifteenDays = clone $search->getDepartureDate()->modify("+20 days");

        dump($currentDepartureDate);
        dump($departureDateMoreFifteenDays);

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)

            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)

            ->andWhere("r.departureDate BETWEEN :currentDepartureDate AND :moreFifteenDaysFromDeparture")
            ->setParameter('currentDepartureDate', $currentDepartureDate)
            ->setParameter('moreFifteenDaysFromDeparture', $departureDateMoreFifteenDays)

            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())

            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())

            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())
            ;
    
            return $query
                ->getQuery()
                ->getScalarResult();
    }

    // Proposition de trajet si aucun résultat (on ne tient compte ici que du point de départ) Filtre Date
    public function otherDayDate($search, $request)
    {
        $start = $request->query->get("startOtherDayDate");
        $limit = $request->query->get("limit");

        $currentDepartureDate = clone $search->getDepartureDate()->modify("-20 days");
        $departureDateMoreFifteenDays = clone $search->getDepartureDate()->modify("+20 days");

        dump($currentDepartureDate);
        dump($departureDateMoreFifteenDays);


        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)

            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)

            ->andWhere("r.departureDate BETWEEN :currentDepartureDate AND :moreFifteenDaysFromDeparture")
            ->setParameter('currentDepartureDate', $currentDepartureDate)
            ->setParameter('moreFifteenDaysFromDeparture', $departureDateMoreFifteenDays)

            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())

            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())

            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())

            ->setFirstResult($start)
            ->setMaxResults($limit)

            ->orderBy('r.departureDate');
    
            return $query
                ->getQuery()
                ->getResult();
    }

    // Proposition de trajet si aucun résultat (on ne tient compte ici que du point de départ) Filtre ville de départ
    public function otherDayCityDeparture($search, $request)
    {
        $start = $request->query->get("startOtherDayCityDeparture");
        $limit = $request->query->get("limit");

        $currentDepartureDate = clone $search->getDepartureDate()->modify("-10 days");
        $departureDateMoreFifteenDays = clone $search->getDepartureDate()->modify("+20 days");

        dump($currentDepartureDate);
        dump($departureDateMoreFifteenDays);

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->addSelect('((ACOS(SIN(:cityLat * PI() / 180) * SIN(r.cityLatitude * PI() / 180) + COS(:cityLat * PI() / 180) * COS(r.cityLatitude * PI() / 180) * COS((:cityLng - r.cityLongitude) * PI() / 180)) * 180 / PI())) as HIDDEN cityDistance')
            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)
            ->setParameter('cityLat', $cityLatitude)
            ->setParameter('cityLng', $cityLongitude)

            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)

            ->andWhere("r.departureDate BETWEEN :currentDepartureDate AND :moreFifteenDaysFromDeparture")
            ->setParameter('currentDepartureDate', $currentDepartureDate)
            ->setParameter('moreFifteenDaysFromDeparture', $departureDateMoreFifteenDays)

            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())

            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())

            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())

            ->setFirstResult($start)
            ->setMaxResults($limit)

            ->orderBy('cityDistance');
    
            return $query
                ->getQuery()
                ->getResult();
    }

    // Proposition de trajet si aucun résultat (on ne tient compte ici que du point de départ) Filtre spot d'arrivée
    public function otherDaySpotArrival($search, $request)
    {
        $start = $request->query->get("startOtherDaySpotArrival");
        $limit = $request->query->get("limit");

        $currentDepartureDate = clone $search->getDepartureDate()->modify("-10 days");
        $departureDateMoreFifteenDays = clone $search->getDepartureDate()->modify("+20 days");

        dump($currentDepartureDate);
        dump($departureDateMoreFifteenDays);

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)

            ->innerJoin('r.spot', 'spot')
            ->addSelect('((ACOS(SIN(:spotLat * PI() / 180) * SIN(spot.latitude * PI() / 180) + COS(:spotLat * PI() / 180) * COS(spot.latitude * PI() / 180) * COS((:spotLng - spot.longitude) * PI() / 180)) * 180 / PI())) as HIDDEN spotDistance')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)
            ->setParameter('spotLat', $spotLatitude)
            ->setParameter('spotLng', $spotLongitude)

            ->andWhere("r.departureDate BETWEEN :currentDepartureDate AND :moreFifteenDaysFromDeparture")
            ->setParameter('currentDepartureDate', $currentDepartureDate)
            ->setParameter('moreFifteenDaysFromDeparture', $departureDateMoreFifteenDays)

            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())

            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())

            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())

            ->setFirstResult($start)
            ->setMaxResults($limit)

            ->orderBy('spotDistance');
    
            return $query
                ->getQuery()
                ->getResult();
    }

    // Proposition de trajet si aucun résultat (on ne tient compte ici que du point de départ) Filtre Prix
    public function otherDayPrice($search, $request)
    {
        $start = $request->query->get("startOtherDayPrice");
        $limit = $request->query->get("limit");

        $currentDepartureDate = clone $search->getDepartureDate()->modify("-10 days");
        $departureDateMoreFifteenDays = clone $search->getDepartureDate()->modify("+20 days");

        dump($currentDepartureDate);
        dump($departureDateMoreFifteenDays);

        $spotLatitude = $search->getSpot()->getLatitude();
        $spotLongitude = $search->getSpot()->getLongitude();

        $spotDistance = '(6378 * acos(cos(radians(' . $spotLatitude . ')) * cos(radians(spot.latitude)) * cos(radians(spot.longitude) - radians(' . $spotLongitude . ')) + sin(radians(' . $spotLatitude . ')) * sin(radians(spot.latitude))))';

        $cityLatitude = $search->getCityLatitude();
        $cityLongitude = $search->getCityLongitude();

        $cityDistance = '(6378 * acos(cos(radians(' . $cityLatitude . ')) * cos(radians(r.cityLatitude)) * cos(radians(r.cityLongitude) - radians(' . $cityLongitude . ')) + sin(radians(' . $cityLatitude . ')) * sin(radians(r.cityLatitude))))';

        $query = $this->createQueryBuilder('r')

            ->where("" . $cityDistance . " < :cityDistance")
            ->setParameter('cityDistance', 30)

            ->innerJoin('r.spot', 'spot')
            ->andWhere("" . $spotDistance . " < :spotDistance")
            ->setParameter('spotDistance', 30)

            ->andWhere("r.departureDate BETWEEN :currentDepartureDate AND :moreFifteenDaysFromDeparture")
            ->setParameter('currentDepartureDate', $currentDepartureDate)
            ->setParameter('moreFifteenDaysFromDeparture', $departureDateMoreFifteenDays)

            ->andWhere('r.availableSeat >= :availableSeat')
            ->setParameter('availableSeat', $search->getAvailableSeat())

            ->andWhere('r.boardMax >= :boardMax')
            ->setParameter('boardMax', $search->getBoardMax())

            ->andWhere('r.boardSizeMax >= :boardSizeMax')
            ->setParameter('boardSizeMax', $search->getBoardSizeMax())

            ->setFirstResult($start)
            ->setMaxResults($limit)

            ->orderBy('r.price');
    
            return $query
                ->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Ride[] Returns an array of Ride objects
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
    public function findOneBySomeField($value): ?Ride
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
