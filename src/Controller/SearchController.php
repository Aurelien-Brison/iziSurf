<?php

namespace App\Controller;

use DateTime;
use App\Entity\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\SearchRideType;
use App\Repository\RideRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/search")
 */
class SearchController extends AbstractController
{   
    /**
     * @Route("/", name="search", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideSearch(Request $request, RideRepository $rideRepository)
    {
        $user = $this->getUser();
        
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }
        
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $defaultDate = new DateTime('1900-01-01');

        $userId = $this->getUser()->getId();

        $search = new Search();

        $searchForm = $this->createForm(SearchRideType::class, $search , ['user' => $userId]);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {

            if (!$search->getReturnDate()) {
                $search->setReturnDate($defaultDate);
            }

            $user = $this->getUser();
            $search->setUser($user);

            $dDay = $rideRepository->dDayTotal($search);
            $otherDay = $rideRepository->otherDayTotal($search);

            $search->getDepartureDate()->modify("-10 days");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($search);
            $entityManager->flush();

            return $this->render('search/result.html.twig', [
                'search'    => $search,
                'dDay'      => $dDay,
                'otherDay'  => $otherDay     
            ]);
        }

        return $this->render('search/search.html.twig', [
            'form' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="search_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideEdit(RideRepository $rideRepository, Request $request, Search $search): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        // if(!$this->isGranted('EDIT', $search)){
        //     throw $this->createNotFoundException('Page introuvable');
        // }

        $user = $this->getUser()->getId();
        $form = $this->createForm(SearchRideType::class, $search , ['user' => $user]);

        $returnDate = $search->getReturnDate();
        $defaultDate = new DateTime('1900-01-01');

        if ($returnDate == $defaultDate) {
            $form->get('returnDate')->setData(null);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $defaultDate = new DateTime('1900-01-01');
            if (!$search->getReturnDate()) {
                $search->setReturnDate($defaultDate);
            }

            $dDay = $rideRepository->dDayTotal($search);
            $otherDay = $rideRepository->otherDayTotal($search);

            $search->getDepartureDate()->modify("-10 days");

            $this->getDoctrine()->getManager()->flush();

            return $this->render('search/result.html.twig', [
                'search'    => $search,
                'dDay'      => $dDay,
                'otherDay'  => $otherDay     
            ]);
            
        }

        return $this->render('search/edit.html.twig', [
            'search'    => $search,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @Route("/{search}/notified", name="search_notified", methods={"GET", "POST"})
     */
    public function searchNotifiedWhenResult(Search $search)
    {
        $search->setIsNotifiedWhenResult(1);

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notifiedWhenResult', 'Tu recevras un email dès qu\'un conducteur publiera le trajet que tu recherches!');
            
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{search}/not-notified", name="search_not-notified", methods={"GET", "POST"})
     */
    public function searchNotNotified(Search $search)
    {
        $search->setIsNotifiedWhenResult(0);

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('stopResultNotifications', 'Ta recherche a bien été désactivée');
            
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/result/{search}/dDay/cityDeparture", name="search_result_dDay_cityDeparture", methods={"GET", "POST"})
     */
    public function searchResultDDayCityDeparture(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->dDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->dDayCityDeparture($search, $request);
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
     * @Route("/result/{search}/dDay/spotArrival", name="search_result_dDay_spotArrival", methods={"GET", "POST"})
     */
    public function searchResultDDaySpotArrival(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->dDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->dDaySpotArrival($search, $request);
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
     * @Route("/result/{search}/dDay/price", name="search_result_dDay_price", methods={"GET", "POST"})
     */
    public function searchResultDDayPrice(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->dDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->dDayPrice($search, $request);
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
    * @Route("/result/{search}/otherDay/date", name="search_result_otherDay_date", methods={"GET", "POST"})
    */
    public function searchResultOtherDayDate(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->otherDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->otherDayDate($search, $request);
        $results = count($rides);
        
        dump($results);
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
    * @Route("/result/{search}/otherDay/cityDeparture", name="search_result_otherDay_cityDeparture", methods={"GET", "POST"})
    */
    public function searchResultOtherDayCityDeparture(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->otherDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->otherDayCityDeparture($search, $request);
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
    * @Route("/result/{search}/otherDay/spotArrival", name="search_result_otherDay_spotArrival", methods={"GET", "POST"})
    */
    public function searchResultOtherDaySpotArrival(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->otherDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->otherDaySpotArrival($search, $request);
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
    * @Route("/result/{search}/otherDay/price", name="search_result_otherDay_price", methods={"GET", "POST"})
    */
    public function searchResultOtherDayPrice(RideRepository $rideRepository, Request $request, Search $search)
    {
        // Compte tous les résultats sans LIMIT
        $ridesTotal = $rideRepository->otherDayTotal($search);
                
        // Tu stockes les résultats de la requête (tu as besoin de $request pour récupérer la valeur des paramètres start et limit envoyé par ajax)
        $rides = $rideRepository->otherDayPrice($search, $request);
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

}