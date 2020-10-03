<?php


namespace App\Service;


use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class MailerService extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct( \Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }
    
    public function contact(Contact $contact)
    {
        
        $message = (new \Swift_Message('Contact : '. $contact->getObject()))
            ->setFrom($contact->getEmail())
            //->setFrom('ramenetaplanche@gmail.com')
            ->setTo('ramenetaplanche@gmail.com')
            /*->setBody($this->renderer->render('emails/contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');*/
            //->setReplayTo($contact->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/contact.html.twig',
                    ['date' => $contact->getDate(),
                        'email' => $contact->getEmail(),
                        'object' => $contact->getObject(),
                        'message' => $contact->getMessage(),
                        'name' => $contact->getLastname().' '. $contact->getFirstname(),
                    ]
                ),
                'text/html'
            );
        
            //dd($message);
        return $this->mailer->send($message);
    }
}