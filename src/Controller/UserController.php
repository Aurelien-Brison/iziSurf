<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/inscription", name="user_registration")
     */
    public function userAdd(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {   
        $user = new User();
        
        $form = $this->createForm(UserType::class, $user);

        dump($request->request->get('user[password]'));
        
        $form->handleRequest($request);
        
        // Si le formulaire est soumis et si il est valide alors je rentre dans la condition
        if ($form->isSubmitted() && $form->isValid()){

            $leurre = $user->getLeurre();

            if (isset($leurre) && !empty($leurre)) {
                throw $this->createAccessDeniedException('Vous ne pouvez pas vous inscrire');
            }
            // Hash du mot de passe
            $hash = $passwordEncoder->encodePassword($user, $user->getPassword());

            // On génère le token d'activation
            $user->setActivationToken(md5(uniqid()));

            // Je dis à mon user de modifier le mot de passe en "hashé"
            $user->setPassword($hash);

            $em->persist($user);
            
            $em->flush();

            $message = (new \Swift_Message('Active ton compte'))
            ->setFrom('ramenetaplanche@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/activation.html.twig', 
                    ['token' => $user->getActivationToken()]
                ),
                'text/html'
            )
            ;
            $mailer->send($message);

            $this->addFlash('activationEmail', 'Rendez-vous maintenant dans ta boîte mail pour activer ton compte');

            return $this->redirectToRoute('app_login');

            
        }       

        return $this->render('user/registration.html.twig', [
            
            'form' => $form->createView(),
            
        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activation($token, UserRepository $userRepository, EntityManagerInterface $em) 
    {
        // On vérifie sur un utilisateur à ce token
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        // Si aucun utilisateur n'existe avec ce token
        if (!$user) {
            // throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
            return $this->redirectToRoute('app_login');
        }

        // On supprime le token
        $user->setActivationToken(null);
        $em->persist($user);
        $em->flush();

        // On envoie un message flash

        $this->addFlash('activationSuccess', 'Ton compte est bien actif, tu peux maintenant te connecter!');

        return $this->redirectToRoute('app_login');

    }

}
