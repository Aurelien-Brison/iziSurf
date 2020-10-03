<?php

namespace App\Controller;

use App\Entity\Fit;
use App\Entity\Ride;
use App\Entity\User;
use App\Repository\FitRepository;
use App\Repository\RideRepository;
use App\Repository\SearchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class FitController extends AbstractController
{
    /**
     * @Route("/fit/{slug}", name="fit", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function index(User $user, RideRepository $rideRepository, FitRepository $fitRepository)
    {
        $user = $this->getUser();
        
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }
        
        if ($this->getUser() == null){
            return $this->redirectToRoute('app_login');
        }
        
        $createdRides = $rideRepository->findUserAllRidesTotal($user);
        $archivedRides = $rideRepository->findUserAllArchivedRidesTotal($user);
        $favouriteRides = $fitRepository->findRideByFavoritesTotal($user);;
        $acceptedRides = $fitRepository->findRideByResponseStatusAcceptedTotal($user);
        $pendingRides = $fitRepository->findRideByResponseStatusWaitingTotal($user);

        return $this->render('fit/currentRide.html.twig', [
            'createdRides'      => $createdRides,
            'archivedRides'     => $archivedRides,
            'favouriteRides'    => $favouriteRides,
            'acceptedRides'     => $acceptedRides,
            'pendingRides'      => $pendingRides,
        ]);
    }

    /**
     * @Route("/fit/{slug}/created-rides", name="fit_created-rides", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function ajaxCreatedRides(User $user, RideRepository $rideRepository, Request $request)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->findUserAllRidesTotal($user);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->findUserAllRides($user, $request);
        $results = count($rides);
        
        // Si le résultat > 1
        if ($results > 0) {
            // Alors tant qu'il y a des résultats, tu envoies tes données en json
            while($rides) {          
                return $this->json(
                    [
                        'rides' => $rides, 
                        'ridesTotal' => $ridesTotal
                    ],
                    Response::HTTP_OK,
                    array(),
                    ['groups' => 'normaliz:myride']
                );
            }
        // S'il n'y a aucun ou plus de résultat; tu renvoies une json Response pour éviter d'avoir une erreur 500 dans tes requêtes
        } else {
            return new JsonResponse($rides);
        }

    }

    /**
     * @Route("/fit/{slug}/archived-rides", name="fit_archived-rides", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function ajaxArchivedRides(User $user, RideRepository $rideRepository, Request $request)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->findUserAllArchivedRidesTotal($user);

        $rides = $rideRepository->findUserAllArchivedRides($user, $request);

        $results = count($rides);
        if ($results > 0) {
            while($rides) {        
                return $this->json(
                     [
                        'rides' => $rides, 
                        'ridesTotal' => $ridesTotal
                    ],
                    Response::HTTP_OK,
                    array(),
                    ['groups' => 'normaliz:myride']
                );
            }
        } else {
            return new JsonResponse($rides);
        }
    }

    /**
     * @Route("/fit/{slug}/favourite-rides", name="fit_favourites-rides", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function ajaxFavouriteRides(User $user, FitRepository $fitRepository, Request $request)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $fitRepository->findRideByFavoritesTotal($user);

        // Va chercher les 3 trajets pour la pagination
        $rides = $fitRepository->findRideByFavorites($user, $request);
        $results = count($rides);
        if ($results > 0) {
            while($rides) {    
                return $this->json(
                    [
                        'rides' => $rides, 
                        'ridesTotal' => $ridesTotal
                    ],
                    Response::HTTP_OK,
                    array(),
                    ['groups' => 'normaliz:myride']
                );
            }
        } else {
            return new JsonResponse($rides);
        }

    }

    /**
     * @Route("/fit/{slug}/accepted-rides", name="fit_accepted-rides", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function ajaxAcceptedRides(User $user, FitRepository $fitRepository, Request $request)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $fitRepository->findRideByResponseStatusAcceptedTotal($user);

        $rides = $fitRepository->findRideByResponseStatusAccepted($user, $request);
        $results = count($rides);
        if ($results > 0) {
            while($rides) {            
                return $this->json(
                    [
                        'rides' => $rides, 
                        'ridesTotal' => $ridesTotal
                    ],
                    Response::HTTP_OK,
                    array(),
                    ['groups' => 'normaliz:myride']
                );
            }
        } else {
            return new JsonResponse($rides);
        }
    }

    /**
     * @Route("/fit/{slug}/pending-rides", name="fit_pending-rides", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function ajaxPendingRides(User $user, FitRepository $fitRepository, Request $request)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $fitRepository->findRideByResponseStatusWaitingTotal($user);

        $rides = $fitRepository->findRideByResponseStatusWaiting($user, $request);
        $results = count($rides);
        if ($results > 0) {
            while($rides) {          
                return $this->json(
                    [
                        'rides' => $rides, 
                        'ridesTotal' => $ridesTotal
                    ],
                    Response::HTTP_OK,
                    array(),
                    ['groups' => 'normaliz:myride']
                );
            }
        } else {
            return new JsonResponse($rides);
        }
    }

    /**

     * @Route("/fit/{idRide<\d+>}/accept/{idUser<\d+>}", name="accept", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function acceptUser(FitRepository $fitRepository, RideRepository $rideRepository, Ride $idRide, User $idUser, EntityManagerInterface $em, \Swift_Mailer $mailer) 
    {
        
        $status = $em->getReference('App\Entity\ResponseStatus', 2);       
        
        $fitData = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

        $rideById = $rideRepository->findBy(['id' => $idRide]);
        
        //j'enlève un siège dispo si le conducteur accepte 
        foreach($rideById as $ride){
            $ride->getAvailableSeat();

            if($ride->getAvailableSeat() === 0){

                $ride->setAvailableSeat(0);

                $em->persist($ride);
            } else {

                $ride->setAvailableSeat($ride->getAvailableSeat() - $fitData[0]->getNumberPlacesRequested());
                $em->persist($ride);
                $em->flush();
            }

        }

        foreach($fitData as $fitDatas){

            $fitDatas->setStatus($status);

            $em->persist($fitDatas);
    
            $em->flush();
            
        }
        $url = $this->generateUrl('summary_ride', ['id' => $idRide->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = (new \Swift_Message('Ta demande a été acceptée'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($idUser->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/enquiry-accepted.html.twig', 
                            [
                                'url'   => $url,
                                'idRide' => $idRide,
                                'idUser'    => $idUser
                            ]
                        ),
                        'text/html'
                    )
                    ;
        $mailer->send($message);
        
        $this->addFlash('enquiryAccepted', 'La demande du passager a bien été acceptée !');

        return $this->redirectToRoute('summary_ride', [
            'id' => $idRide->getId()
        ]);
        
    }

    /**
     * @Route("/fit/{idRide<\d+>}/refuse/{idUser<\d+>}", name="refuse", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function refuseUser(FitRepository $fitRepository, Ride $idRide, User $idUser, EntityManagerInterface $em, \Swift_Mailer $mailer)
    {
        
        $status = $em->getReference('App\Entity\ResponseStatus', 3);       
        
        $fitData = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

        foreach($fitData as $fitDatas){

            $fitDatas->setStatus($status);

            $em->persist($fitDatas);
    
            $em->flush();
            
        }

        $url = $this->generateUrl('search', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new \Swift_Message('Oh no... Ta demande n\'a pas été acceptée'))
        ->setFrom('ramenetaplanche@gmail.com')
        ->setTo($idUser->getEmail())
        ->setBody(
            $this->renderView(
                'emails/enquiry-refused.html.twig', 
                [
                    'url'       => $url,
                    'idRide'    => $idRide,
                    'idUser'    => $idUser
                ]
            ),
            'text/html'
        )
        ;
        $mailer->send($message);

        $this->addFlash('refusedPassenger', 'Le passager a bien été refusé');

        return $this->redirectToRoute('summary_ride', [
            'id' => $idRide->getId()
        ]);
        
    }

    /**
     * @Route("/fit/{idRide<\d+>}/cancel/{idUser<\d+>}", name="cancel", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function cancelRide(FitRepository $fitRepository, Ride $idRide, User $idUser, EntityManagerInterface $em, \Swift_Mailer $mailer, Request $request)
    {      
        
        $fitData = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

        foreach($fitData as $fitDatas){      
  
            $idRide->setAvailableSeat($idRide->getAvailableSeat() + $fitDatas->getNumberPlacesRequested());
            $em->remove($fitDatas);
    
            $em->flush();
            
        }

        $cancel = $request->request->get('cancel');

        $message = (new \Swift_Message('Une personne a annulé sa réservation'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($idRide->getDriver()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/cancel-booking.html.twig', 
                            [
                                'cancel'    => $cancel,
                                'idRide'    => $idRide,
                                'idUser'    => $idUser
                            ]
                        ),
                        'text/html'
                    )
                    ;
        $mailer->send($message);

        $this->addFlash('warning', 'Ta réservation a bien été annulée');

        return $this->redirectToRoute('summary_ride', [
            'id' => $idRide->getId()
        ]);
        
    }

    /**
     * @Route("/fit/{idRide<\d+>}/cancel/enquiry/{idUser<\d+>}", name="cancel_enquiry", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function cancelEnquiryRide(FitRepository $fitRepository, Ride $idRide, User $idUser, EntityManagerInterface $em)
    {      
        
        $fitData = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

        foreach($fitData as $fitDatas){      
  
            $em->remove($fitDatas);
    
            $em->flush();
            
        }

        $this->addFlash('cancelEnquiry', 'Ta demande de résa a bien été annulée');

        return $this->redirectToRoute('summary_ride', [
            'id' => $idRide->getId()
        ]);
        
    }

    /**
     * @Route("/fit/{idRide<\d+>}/book/{idUser<\d+>}", name="book", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function bookRide(Ride $idRide, User $idUser, EntityManagerInterface $em, SearchRepository $searchRepository, FitRepository $fitRepository, RideRepository $rideRepository ,\Swift_Mailer $mailer, Request $request)
    {      
     
            $url = $this->generateUrl('summary_ride', ['id' => $idRide->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
  
            $status = $em->getReference('App\Entity\ResponseStatus', 1);

            $dataFit = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

           
            $numberPlacesRequested = $request->request->get('numberPlaces');
            if(!empty($dataFit)){

                foreach($dataFit as $data){

                    $data->setNumberPlacesrequested($numberPlacesRequested);
                    $data->setStatus($status);
                    $em->persist($data);
                    
                }
                
                $message = (new \Swift_Message('Quelqu\'un veut réserver sa place sur ton trajet'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($idRide->getDriver()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/booking-enquiry.html.twig', 
                            [
                                'url'   => $url,
                                'idRide' => $idRide,
                                'idUser'    => $idUser
                            ]
                        ),
                        'text/html'
                    )
                    ;
                $mailer->send($message);
                $em->flush();

                $this->addFlash('rideRequest', 'Ta demande est bien partie! Le conducteur va recevoir une notification et te donnera une réponse très vite.');

                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]);

            } 

            if($dataFit === []){

                $newfit = new Fit();
                $newfit->setNumberPlacesrequested($numberPlacesRequested);
                $newfit->setUser($idUser);
                $newfit->setRide($idRide);
                $newfit->setstatus($status);
                $newfit->setIsFavorite(0);

                $em->persist($newfit);
                $em->flush();

                $message = (new \Swift_Message('Quelqu\'un veut réserver sa place sur ton trajet'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($idRide->getDriver()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/booking-enquiry.html.twig', 
                            [
                                'url'   => $url,
                                'idRide' => $idRide,
                                'idUser' => $idUser
                            ]
                        ),
                        'text/html'
                    )
                    ;
                $mailer->send($message);

                $this->addFlash('message', 'Vous avez effectué une demande de reservation. Veuillez attendre que le conducteur accepte votre demande');
                
                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]); 
            }
            else {

                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]);
            }
                   
        
    }

     /**
     * @Route("/fit/{idRide<\d+>}/favorite/{idUser<\d+>}", name="favorite", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function favoriteRide(FitRepository $fitRepository, Ride $idRide, User $idUser, EntityManagerInterface $em)
    {      
            $dataFit = $fitRepository->fetchFitByRideIdAndUserId($idRide, $idUser);

            if(!empty($dataFit)){

                foreach($dataFit as $data){
                    if($data->getIsFavorite() === true){
                        
                        $data->setIsFavorite(0);

                    } elseif($data->getIsFavorite() === false) {
                        
                        $data->setIsFavorite(1);
                    }
                    $em->persist($data);
                    
                }
                $em->flush();
                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]);

            }  
            if($dataFit === []){

                $newfit = new Fit();
                
                $newfit->setUser($idUser);
                $newfit->setRide($idRide);
                $newfit->setIsFavorite(1);
                
                $em->persist($newfit);
                $em->flush();
                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]); 
            }
            else {
                
                return $this->redirectToRoute('summary_ride', [
                    'id' => $idRide->getId()
                ]);
            }     
            
    }

    

    /**
     * @Route("fit/{id}/delete", name="fit_delete_by_ride_id", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function fitDeleteByRideId( Ride $ride, FitRepository $fitRepository)
    {

            $findByRideId = $fitRepository->findBy(['ride' => $ride]);
            
            foreach( $findByRideId as $removeRideId){

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($removeRideId);        
            }
                $entityManager->flush();

            return $this->redirectToRoute('home');

    }

  
    /**
     * @Route("message/{id}/delete", name="message_delete_by_ride_id", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function messageDeleteByRideId( Ride $ride, FitRepository $fitRepository)
    {
          $findByRideId = $fitRepository->findBy(['ride' => $ride]);
          foreach( $findByRideId as $removeRideId){
              $entityManager = $this->getDoctrine()->getManager();
              $entityManager->remove($removeRideId);        
          }
              $entityManager->flush();
          return $this->redirectToRoute('home');
    }
}
