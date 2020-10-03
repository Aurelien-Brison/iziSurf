<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MissingEmail;
use App\Form\ResetPassType;
use App\Form\ChangePassType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // if ($this->getUser()->getActivationToken() != null){
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/mot-de-passe-oublie", name="security_forgotten_password")
     */
    public function forgottenPass(Request $request, UserRepository $userRepository, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $form = $this->createForm(ResetPassType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $donnees = $form->getData();

            $user = $userRepository->findOneByEmail($donnees['email']);

            if(!$user){
                
                $this->addFlash('noEmailAddress', 'cette adresse n\'existe pas');

               return $this->redirectToRoute('app_login');
            }

            // je génère un token
            $token = $tokenGenerator->generateToken();

            try{
                $user->setToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }catch(\Exception $e){
                $this->addFlash('warning', 'une erreur est survenue : '. $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            // On génère l'url de réinitialisation de mot de passe
            $url = $this->generateUrl('app_resest_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // on envoie le message 
            $message = (new \Swift_Message('Mot de passe oublié'))
            ->setFrom('ramenetaplanche@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/forgotten-password.html.twig', 
                    [
                        'url'   => $url
                    ]
                ),
                'text/html'
            )
            ;
            
            $mailer->send($message);

            // Message flash de confirmation

            $this->addFlash('forgottenPassword', 'On t\'a envoyé un mail pour réinitialiser ton mot de passe');

            return $this->redirectToRoute('app_login');

        }

        return $this->render('security/forgotten_password.html.twig', [

            'emailForm' => $form->createView()
        ]);
    }

    /**
     * @Route("reinitialisation-mot-de-passe/{token}", name="app_resest_password")
     */
    public function resetPassword($token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {   

        $form = $this->createForm(ChangePassType::class);
        // On chercher l'utilisateur avec le token fourni
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $token]);

        
        if(!$user) {
            // $this->addFlash('danger', 'Token inconnu');
            return $this->redirectToRoute('app_login');
        }

        
        if($request->isMethod('POST')){

             // On supprime le token
             $user->setToken(null);
             // on chiffre le mot de passe
             $requete = $request->request->get('change_pass');
             
             $user->setPassword($passwordEncoder->encodePassword($user,  $requete['password']));
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($user);
             $entityManager->flush();
 
             $this->addFlash('newPassword', 'Ton mot de passe a bien été modifié, tu peux désormais te connecter avec');
 
             return $this->redirectToRoute('app_login');                  

        }    
        
            
        else {
            return $this->render('security/reset_password.html.twig', 
            [
                'token' => $token,
                'form' => $form->createView()
             ]);
        }
    }

    /**
     * @Route("/absence/email", name="missing-email")
     */
    public function missingEmail(Request $request, UserRepository $userRepository, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(MissingEmail::class);

        $form->handleRequest($request);

        

        if ($form->isSubmitted() && $form->isValid()){

            $donnees = $form->getData();

            $user = $userRepository->findOneByEmail($donnees['email']);

            if(!$user){
                
                $this->addFlash('noEmailAddress', 'cette adresse n\'existe pas');

               return $this->redirectToRoute('app_login');
            }

            if($user->getActivationToken() === null) {
                $this->addFlash('noEmailAddress', 'Vous avez déjà activé votre compte');
                return $this->redirectToRoute('app_login');
            } else {
                // $url = $this->generateUrl('activation', ['token' => $user->getActivationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
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

        }

        return $this->render('security/missing_email.html.twig', [

            'emailForm' => $form->createView()
        ]);
    }
}
