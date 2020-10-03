<?php

namespace App\Controller;


use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailerService;
use App\Repository\RideRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(RideRepository $rideRepository)
    {
        $user = $this->getUser();

        if ($user) {
            if ($user->getActivationToken()) {
                throw $this->createAccessDeniedException('Vous devez activer votre compte');
            }
        }

        $rides = $rideRepository->findBy([], ['createdAt' => 'DESC'], 4, 0);

        return $this->render('main/home.html.twig', [
            'rides' => $rides,
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, MailerService $mailer)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $mailer->contact($contact);
            
            $this->addFlash("contactEmail", "Ton message a bien été envoyé. Nous te répondrons sous 48h max.");

            return $this->redirectToRoute('home');
        }

        return $this->render('main/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/qui-sommes-nous", name="team")
     */
    public function team()
    {
        return $this->render('main/team.html.twig');
    }

    /**
     * @Route("/conditions-generales-utilisation", name="termsAndConditions")
     */
    public function termsAndConditions()
    {
        return $this->render('main/termsAndConditions.html.twig');
    }

    /**
     * @Route("/404", name="page404")
     */
    public function page404()
    {
        return $this->render('main/page404.html.twig');
    }

    // /**
    //  * @Route("/home-connecte", name="home_connecte")
    //  * @IsGranted("ROLE_USER", message="Accès non autorisé")
    //  */
    // public function homeConnecte(RideRepository $rideRepository)
    // {
    //     $user = $this->getUser();
        
    //     if ($user->getActivationToken()) {
    //         throw $this->createAccessDeniedException('Vous devez activer votre compte');
    //     }

    //     if ($this->getUser() == null){
            
    //         return $this->redirectToRoute('app_login');
    //     }
        
    //     $rides = $rideRepository->findBy([], ['createdAt' => 'DESC'], 4, 0);

    //     return $this->render('main/home_user_co.html.twig', [
    //         'rides' => $rides
    //     ]);
    // }
}
