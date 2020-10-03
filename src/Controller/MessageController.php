<?php

namespace App\Controller;

use App\Entity\Ride;
use App\Entity\User;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    /**
     * @Route("/message/{userId}/send/{rideId}", name="message", methods={"GET", "POST"})
     */
    public function index(User $userId, Ride $rideId, Request $request, EntityManagerInterface $em)
    {
        
        
        if($request->request->get('message') == "") {
            return $this->json([
                'Error' => 'message vide'
            ]);
        }
            
            $inputMessage = htmlspecialchars($request->request->get('message'));
            $message = New Message();
            $message->setUser($userId);
            $message->setRide($rideId);
            $message->setContent($inputMessage);
            $message->setCreatedAt(new \DateTime('now'));
            $em->persist($message);
            $em->flush($message);

            return $this->json([
                'success' => 'Message bien ajouté'
            ]);    

    }

    /**
     * @Route("/message/{userId}/load/{rideId}", name="load", methods={"GET", "POST"})
     */
    public function load(Ride $rideId, MessageRepository $messageRepository,Request $request)
    {

        $lastMessage = $messageRepository->messageByUserAndRide($rideId);

        if($lastMessage == []){
            return $this->json([
                'message' => "pas de message"
            ]);   
        } else {

            $tableauMessage = [$lastMessage[0]->getId(),$lastMessage[0]->getUser()->getId() , $lastMessage[0]->getUser()->getFirstName(),$lastMessage[0]->getContent(), $lastMessage[0]->getCreatedAt()];
            return $this->json([
                $tableauMessage
            ]);   
        }
 
        
        
    }



}

