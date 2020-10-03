<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\User;
use App\Form\CarType;
use App\Form\UserType;
use App\Form\ProfileType;
use App\Entity\ProfileInfo;
use App\Form\ProfileInfoType;
use Doctrine\ORM\EntityManager;
use App\Form\ProfilePictureType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ProfileController extends AbstractController
{
   
    // /**
    //  * @Route("/profil/{id}", name="profil")
    //  * @IsGranted("ROLE_USER", message="Accès non autorisé")
    //  */
    // public function profil(User $user, UserRepository $userRepository)
    // {
    //     if ($this->getUser() == null){

    //         return $this->redirectToRoute('app_login');
    //     }

    //     $id = $user->getId();
    //     $userCurrent =  $this->getUser()->getId();
    //     if ($userCurrent !== $id){
    //         throw $this->createNotFoundException('Page introuvable');
    //     }


    //     $userRepo = $userRepository->findBy(['id' => $user]);

    //     return $this->render('profil/profil.html.twig', [
    //         'user' => $userRepo
    //     ]);
    // }

    /**
     * @Route("profil/{slug}", name="profile", methods={"GET","POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function editProfile(Request $request, User $user, UserPasswordEncoderInterface $userPasswordEncoder, EntityManagerInterface $em): Response
    {

        if ($this->getUser() == null){
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        if ($user->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }

        $id = $user->getId();

        $userCurrent =  $this->getUser()->getId();
        if ($userCurrent !== $id){
            throw $this->createNotFoundException('Page introuvable');
        }

        // Formulaire pour éditer le profil
        $profileForm = $this->createForm(ProfileType::class, $user);

        // On remove les champs que l'on ne veut pas afficher pour éditer le profil
        $profileForm->remove('password');
        $profileForm->remove('confirmPassword');
        $profileForm->remove('imageFile');

        // On remplit quand même leurs valeurs pour ne pas avoir d'erreur
        $userPassword = $user->getPassword();

        if ($userPassword) {
            $user->setPassword($userPassword);
            $user->setConfirmPassword($userPassword);
        }

        // Et on traite les données
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {

            // // on va chercher la donnée du password non mappé
            // $newPassword = $form->get('password')->getData();
            
            // if (!empty($newPassword)){

            //     $passwordHash = $userPasswordEncoder->encodePassword($user, $user->getPassword());
            //     // On écrase le mot de passe du $user avec le mot de passe haché
            //     $user->setPassword($passwordHash);
            // }
            
            $user->setUpdatedAt(new \DateTime('now'));
           
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('profileSuccess', 'Profil modifié !');

            return $this->redirectToRoute('profile', ['slug' => $user->getSlug()]);
        }

        // Formulaire pour modifier la photo de profil
        $pictureForm = $this->createForm(ProfilePictureType::class, $user);

        // On remove les champs que l'on ne veut pas afficher pour éditer le profil
        $pictureForm->remove('firstname');
        $pictureForm->remove('lastname');
        $pictureForm->remove('age');
        $pictureForm->remove('gender');
        $pictureForm->remove('phone');
        $pictureForm->remove('email');
        $pictureForm->remove('password');
        $pictureForm->remove('confirmPassword');
        $pictureForm->remove('level');
        
        // On remplit quand même leurs valeurs pour ne pas avoir d'erreur
        $userPassword = $user->getPassword();

        if ($userPassword) {
            $user->setPassword($userPassword);
            $user->setConfirmPassword($userPassword);
        }

        // Et on traite les données
        $pictureForm->handleRequest($request);

        if ($pictureForm->isSubmitted() && $pictureForm->isValid()) {
            
            $user->setUpdatedAt(new \DateTime('now'));
           
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('pictureSuccess', 'Photo modifiée !');

            return $this->redirectToRoute('profile', ['slug' => $user->getSlug()]);
        }

        // Formulaire pour ajouter une voiture

        $userId = $this->getUser()->getId();

        $EntityUser = $em->getReference('App\Entity\User', $userId); 

        $car = new Car();
        
        $carForm = $this->createForm(CarType::class, $car);

        $carForm->handleRequest($request);

        if ($carForm->isSubmitted() && $carForm->isValid()) {

            $car->setUser($EntityUser);

            $em->persist($car);

            $em->flush();

            $this->addFlash('addCarSuccess', 'Voiture ajoutée !');

            return $this->redirectToRoute('profile', ['slug' => $user->getSlug()]);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm->createView(),
            'pictureForm' => $pictureForm->createView(),
            'carForm' => $carForm->createView(),
        ]);
        
    }

    /**
     * @Route("/profil/{id}/supprime", name="profile_delete", methods={"GET", "POST", "DELETE"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function profileDelete(Request $request, User $user): Response
    {
        
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            
            $this->get('security.token_storage')->setToken(null);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
    
            $this->addFlash('deleteProfileSuccess', 'Ton profil a bien été supprimé. À une prochaine sur la vague !');
    
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/vehicule/profil/{id}", name="profile_car_delete", methods={"POST"})
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function delete(Request $request, Car $car): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

            $user = $this->getUser()->getId();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($car);
            $entityManager->flush();

            $this->addFlash('removeCarSuccess', 'Voiture supprimée !');
        
        return $this->redirectToRoute('profile', array('slug' => $this->getUser()->getSlug()));
    }

}

