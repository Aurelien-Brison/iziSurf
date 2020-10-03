<?php

namespace App\Controller;

use DateTime;
use App\Entity\Ride;
use App\Form\RideType;
use App\Repository\FitRepository;
use App\Repository\SearchRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/trajet")
 */
class RideController extends AbstractController
{
    /**
     * @Route("/ajout", name="ride_add", methods={"GET","POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideAdd(Request $request, SearchRepository $searchRepository, \Swift_Mailer $mailer): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }

        $defaultDate = new DateTime('1900-01-01');
        $driver = $this->getUser();

        $user = $this->getUser()->getId();
        
        $ride = new Ride();
        $form = $this->createForm(RideType::class, $ride , ['user' => $user]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ride->setDriver($driver);
            $ride->getCar()->setUser($driver);

            if (!$ride->getReturnDate()) {
                $ride->setReturnDate($defaultDate);
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ride);
            $entityManager->flush();

            // Je vérifie si ce nouveau trajet est recherché par des utilisateurs
            $rideWanted = $searchRepository->rideWanted($ride);

            $url = $this->generateUrl('summary_ride', ['id' => $ride->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            // Si oui, je leur envoie un email
            if ($rideWanted) {
                foreach($rideWanted as $rideSearch) {
                    $message = (new \Swift_Message('Nouveau résultat pour ta recherche de trajet!'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($rideSearch->getUser()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/result-notification.html.twig',
                            [
                                'url'           => $url,
                                'ride'          => $ride,
                                'rideSearch'    => $rideSearch
                            ]
                        ),
                        'text/html'
                    );
                    $mailer->send($message);
                }
            }

            // Et je redirige mon driver vers la page mes trajets
            $this->addFlash('rideAddSuccess', 'Trajet ajouté !');
            
            return $this->redirectToRoute('fit', ['slug' => $driver->getSlug()]);
        }

        return $this->render('ride/add.html.twig', [
            'ride' => $ride,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="ride_show", methods={"GET"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideShow(Ride $ride): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }
       
        return $this->render('ride/show.html.twig', [
            'ride'      => $ride
        ]);

    }

    /**
     * @Route("/{slug}/edit", name="ride_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideEdit(Request $request, Ride $ride): Response
    {

        $user = $this->getUser();
        
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }
        
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        if(!$this->isGranted('EDIT', $ride)){
            throw $this->createNotFoundException('Page introuvable');
        }

        $user = $this->getUser()->getId();
        $form = $this->createForm(RideType::class, $ride , ['user' => $user]);

        $returnDate = $ride->getReturnDate();
        $defaultDate = new DateTime('1900-01-01');

        if ($returnDate == $defaultDate) {
            $form->get('returnDate')->setData(null);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $defaultDate = new DateTime('1900-01-01');
            if (!$ride->getReturnDate()) {
                $ride->setReturnDate($defaultDate);
            }

            $driver = $this->getUser();

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('rideAddSuccess', 'Trajet modifié !');
            
            return $this->redirectToRoute('fit', ['slug' => $driver->getSlug()]);
        }

        return $this->render('ride/edit.html.twig', [
            'ride' => $ride,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/supprime", name="ride_delete", methods={"POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function rideDelete(Ride $ride, Request $request, FitRepository $fitRepository, Swift_Mailer $mailer): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $usersAccept = $fitRepository->findUserByAcceptRide($ride);

        if($usersAccept != []) {

            foreach($usersAccept as $userAccept){
    
                $url = $this->generateUrl('search', [], UrlGeneratorInterface::ABSOLUTE_URL);

                $cancel = $request->request->get('delete');

                // on envoie le message 
                $message = (new \Swift_Message('Oh no... Trajet annulé'))
                ->setFrom('ramenetaplanche@gmail.com')
                ->setTo($userAccept->getUser()->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/cancel-ride.html.twig', 
                        [
                            'url'           => $url,
                            'cancel'        => $cancel,
                            'ride'          => $ride,
                            'userAccept'    => $userAccept
                        ]
                    ),
                    'text/html'
                )
                ;
                
                $mailer->send($message);
            }
        }
       
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($ride);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }

    
}
