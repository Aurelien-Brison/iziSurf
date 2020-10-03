<?php 

namespace App\Controller;

use App\Entity\Ride;
use App\Entity\User;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReviewController extends AbstractController
{
    

    /**
     * @Route("/avis/{idAuthor}/envoye/{idUser}/trajet/{idRide}", name="add_comment")
     */
    public function addComment(Request $request, User $idAuthor, User $idUser, Ride $idRide,  EntityManagerInterface $em, \Swift_Mailer $mailer, ReviewRepository $reviewRepository)
    {

        if ($this->getUser() == null) {

            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()->getActivationToken() !== null) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }

        $reviewByAuthorUserAndRide = $reviewRepository->findReviewByUserAuthorAndRide($idAuthor, $idUser, $idRide);

        if($reviewByAuthorUserAndRide !== []) {
            $this->addFlash('warning', 'Tu as déjà laissé un avis');
            return $this->redirectToRoute('home');
        }

        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
                $review->setAuthor($idAuthor);
                $review->setUser($idUser);
                $review->setRide($idRide);
                $em->persist($review);
                $em->flush();
                $url = $this->generateUrl('review', ['user' => $idUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                $message = (new \Swift_Message('Quelqu\'un a publié un avis qui te concerne'))
                    ->setFrom('ramenetaplanche@gmail.com')
                    ->setTo($idUser->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/review-show.html.twig', 
                            [
                                'url'       => $url,
                                'idRide'    => $idRide,
                                'idAuthor'  => $idAuthor,
                                'idUser'    => $idUser
                            ]
                        ),
                        'text/html'
                    )
                    ;
                    $mailer->send($message);

                return $this->redirectToRoute('home');
        }


        return $this->render('review/review.html.twig', [
        'form'      => $form->createView(),
        'idUser'    => $idUser
        ]);
       
    }

    /**
     * @Route("avis/{user}", name="review")
     */
    public function review(User $user, ReviewRepository $reviewRepository)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()->getActivationToken()) {
            throw $this->createAccessDeniedException('Vous devez activer votre compte');
        }

        $allReviews = $reviewRepository->findReviewByUser($user);
        
        return $this->render('review/allreview.html.twig', [
            'allReviews' => $allReviews
        ]);

    }
}