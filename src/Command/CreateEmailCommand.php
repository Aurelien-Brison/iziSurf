<?php 

namespace App\Command;

use App\Repository\RideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CreateEmailCommand extends command 
{
    protected static $defaultName = 'app:create-email';

    private $mailer;
    private $rideRepository;
    private $em;
    private $router;
    private $twig;


    public function __construct(\Swift_Mailer $mailer, RideRepository $rideRepository, EntityManagerInterface $em, RouterInterface $router, Environment $twig)
    {

        $this->mailer = $mailer;
        $this->rideRepository = $rideRepository;
        $this->em = $em;
        $this->router = $router;
        $this->twig = $twig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Creates a new email')
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Displays more information');
            
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->sendMessage();

        return 0;
    }



    private function sendMessage ()
    {

        $allride = $this->rideRepository->findAll();
        
        foreach($allride as $ride)
        {
            if($ride->getCompleted() !== true){
                if($ride->getDepartureDate() < New \DateTime('now')){
                    $ride->setCompleted(true);
                    $this->em->flush();
                    foreach($ride->getFits() as $fit){
                        if($fit->getStatus() != null) {
                            if($fit->getStatus()->getOrderResponseStatus() == 2) {
                                $email = $fit->getUser()->getEmail();
                                $driver = $ride->getDriver()->getId();
                                $idAuthor = $fit->getUser()->getId();
                                $url = $this->router->generate('add_comment', ['idAuthor' => $idAuthor, 'idUser' => $driver, 'idRide' => $ride->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                                $message = (new \Swift_Message('Alors ce trajet, c\'était comment ?'))
                                ->setFrom('ramenetaplanche@gmail.com')
                                ->setTo($email)
                                ->setBody(
                                    $this->twig->render(
                                        'emails/review-driver.html.twig', 
                                        [
                                            'url'         => $url,
                                            'ride'        => $ride,
                                            'fit'         => $fit,
                                            'idAuthor'    => $idAuthor,
                                        ]
                                    ),
                                    'text/html'
                                )
                                ;
                                $this->mailer->send($message);
                            }
                        }
                    }
    
                    $driver = $ride->getDriver()->getEmail();
                    $idDriver = $ride->getDriver()->getId();
                    $users = '';
                    foreach($ride->getFits() as $fit){
                        $userId = $fit->getUser()->getid();
                        $url = $this->router->generate('add_comment', ['idAuthor' => $idDriver, 'idUser' => $userId, 'idRide' => $ride->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                        $users .= "<p><a href=". $url.">clique ici pour noter ". $fit->getUser()->getFirstname()."</a></p>";
                        
                    }
                    $message = (new \Swift_Message('Alors ce trajet, c\'était comment ?'))
                        ->setFrom('ramenetaplanche@gmail.com')
                        ->setTo($driver)
                        ->setBody(
                            $this->twig->render(
                                'emails/review-passenger.html.twig', 
                                [
                                    'url'         => $url,
                                    'ride'        => $ride,
                                    'users'       => $users,
                                ]
                            ),
                            'text/html'
                        )
                        ;
                        $this->mailer->send($message);
    
                }

            }
        }


      
    }
}