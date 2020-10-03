<?php

namespace App\Controller;

use App\Entity\Ride;
use App\Repository\FitRepository;
use App\Repository\MessageRepository;
use App\Repository\RideRepository;
use App\Repository\SpotRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class SummaryRideController extends AbstractController
{
    /**
     * @Route("/resume/trajet/{id}", name="summary_ride")
     * @IsGranted("ROLE_USER", message="Accès non autorisé")
     */
    public function index(Ride $ride, RideRepository $rideRepository, FitRepository $fitRepository, SpotRepository $spot, MessageRepository $messageRepository)
    {
        if ($this->getUser() == null){
            return $this->redirectToRoute('app_login');
        }
        
        if ($this->getUser()->getActivationToken()) {
            throw $this->createAccessDeniedException('Tu dois activer ton compte. On t\'a envoyé un mail il y a quelques jours.');
        }

        $currentRide = $rideRepository->findOneBy(['id' => $ride]);
        $currentFit = $fitRepository->fetchFitByRideIdAndUserIdWithoutEntityUser($ride, $this->getUser()->getId());
        
        if($currentFit != [] && $currentFit[0]->getstatus() != null ){

            $currentStatus = $currentFit[0]->getstatus()->getName();         
        } else {
            $currentStatus = '';
        } 

        $peoplesWait = $fitRepository->findUserByWaitRide($ride);
        $peoplesAccept = $fitRepository->findUserByAcceptRide($ride);
        $peoplesRefuse = $fitRepository->findUserByRefuseRide($ride);
        $allFit = $fitRepository->findAll();  
        $allMessages = $messageRepository->findAll();
      
        return $this->render('summary_ride/index.html.twig', [
            'currentRide' => $currentRide,
            'peoplesWait' => $peoplesWait,
            'peoplesAccept' => $peoplesAccept,
            'peoplesRefuse' => $peoplesRefuse,
            'allFit' => $allFit,
            'currentStatus' => $currentStatus, 
            'currentFit' => $currentFit,
            'allMessages' => $allMessages
        ]);
    }
}
