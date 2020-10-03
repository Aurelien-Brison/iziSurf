<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserTypeBackOffice;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;



/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN", message="Accès non autorisé")
 */
class UserBackOfficeController extends AbstractController
{
    /**
     * @Route("/user", name="back_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        // if ($this->getUser() == null){
            
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->render('back_office/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/user/new", name="back_user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        $form = $this->createForm(UserTypeBackOffice::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('back_user_index');
        }

        return $this->render('back_office/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}", name="back_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('back_office/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="back_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $userPasswordEncoder): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserTypeBackOffice::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUpdatedAt(new \DateTime('now'));
           
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès!');
            return $this->redirectToRoute('back_user_index');
        }

        return $this->render('back_office/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}", name="back_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->getUser() == null){
            
            return $this->redirectToRoute('app_login');
        }
        
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_user_index');
    }
}
