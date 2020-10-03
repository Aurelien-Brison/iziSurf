<?php

namespace App\Controller;

use App\Entity\Car;
use App\Form\CarType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class CarController extends AbstractController
{
    
    /**
     * @Route("/utilisateur/profil/ajout/vehicule", name="profil_car")
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function addCar(EntityManagerInterface $em, Request $request)
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser()->getId();

        $EntityUser = $em->getReference('App\Entity\User', $user); 

        $car = new Car();
        
        $form = $this->createForm(CarType::class, $car);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $car->setUser($EntityUser);
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('ride_add');
        }

        return $this->render('car/car.html.twig', [

            'form' => $form->createView(),
        ]);
    }
    

    // /**
    //  * @Route("/vehicule/{id}", name="car_delete", methods={"DELETE"})
    //  * @IsGranted("ROLE_USER", message="Accès non autorisé")
    //  */
    // public function delete(Request $request, Car $car): Response
    // {
    //     if ($this->getUser() == null){
            
    //         return $this->redirectToRoute('app_login');
    //     }

    //     if ($this->isCsrfTokenValid('delete'.$car->getId(), $request->request->get('_token'))) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->remove($car);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('home');
    // }
}
