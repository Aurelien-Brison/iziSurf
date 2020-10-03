<?php

namespace App\Controller;

use DateTime;
use App\Entity\Ride;
use App\Form\RideType;
use App\Repository\RideRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN", message="Accès non autorisé")
 */
class RideBackController extends AbstractController
{


  /**
     * @Route("/ride", name="back_ride_index", methods={"GET"})
     */
    public function index(RideRepository $rideRepository): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('back_office/ride/index.html.twig', [
            'rides' => $rideRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ride/new", name="back_ride_new", methods={"GET","POST"})
     */
    public function rideAdd(Request $request): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $defaultDate = new DateTime('1900-01-01');
        $driver = $this->getUser();

        $user = $this->getUser()->getId();
        
        $ride = new Ride();
        $form = $this->createForm(RideType::class, $ride , ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ride->setDriver($driver);

            if (!$ride->getReturnDate()) {
                $ride->setReturnDate($defaultDate);
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ride);
            $entityManager->flush();

            return $this->redirectToRoute('fit', ['id' => $driver->getId()]);
        }

        return $this->render('ride/add.html.twig', [
            'ride' => $ride,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ride/{id}", name="back_ride_show", methods={"GET"})
     */
    public function rideShow(Ride $ride): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('back_office/ride/show.html.twig', [
            'ride'      => $ride
        ]);
    }

    /**
     * @Route("/ride/{id}/edit", name="back_ride_edit", methods={"GET","POST"})
     */
    public function rideEdit(Request $request, Ride $ride): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        if(!$this->isGranted('EDIT', $ride)){
            $this->addFlash('danger', 'impossible');
            return $this->redirectToRoute('home');
        }

        $user = $this->getUser()->getId();
        $form = $this->createForm(RideType::class, $ride , ['user' => $user]);

        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ride->setUpdatedAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('back_ride_index');
        }

        return $this->render('back_office/ride/edit.html.twig', [
            'ride' => $ride,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ride/delete/{id}", name="back_ride_delete", methods={"GET", "POST"})
     */
    public function rideDelete(Ride $ride)
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($ride);
        $entityManager->flush();

        return $this->redirectToRoute('back_ride_index');
    }

    
}
