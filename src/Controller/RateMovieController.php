<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Reservation;
use App\Entity\ProjectionEvent;
use App\Entity\Movie;
use App\Security\Jwt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\Clock\now;

class RateMovieController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private Jwt $jwtManager,
    ) {}

    #[Route('/api/movies/rate', name: 'app_rate_movie', methods: ['POST'])]
    public function index( Request $request): Response
    {
        $decodedJwtToken = $this->jwtManager->decodeJwt();
        $query = $this->em->createQueryBuilder()
        ->select('u')
        ->from('App\Entity\User', 'u')
        ->where('u.email = :email' )
        ->setParameter('email', $decodedJwtToken['username'])
        ->getQuery();
        $userFromjwt = $query->getOneOrNullResult();

        $content = json_decode($request->getContent(), true);
        if (!$content['reservationId'] || !$content['points'] || !$content['comment']) $this->json(["message" => "Erreur"], 400);
        
        $reservationId = filter_var((int) $content['points'], FILTER_VALIDATE_INT);
        if (!$reservationId) return $this->json(["message" => "Erreur"], 400);
        
        $points = filter_var((int) $content['points'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 5]]);
        if (!$points) return $this->json(["message" => "Les points doivent être entre 0 et 5"], 400);
        

        $comment = htmlspecialchars($content['comment']);

        $reservationRepo = $this->em->getRepository(Reservation::class);
        /** @var Reservation $reservation */
        $reservation = $reservationRepo->findOneBy(['id' => $reservationId]);
        /** @var ProjectionEvent $projection */
        $projection =  $reservation->getProjectionEvent();
        /** @var Movie $movie */
        $movie =  $projection->getMovie();

        $userFromReservation = $reservation->getUser();
        if ($userFromjwt !== $userFromReservation) return $this->json(["message" => "Ce n'est pas votre réservation"], 401);

        // HANDLE ERRORS
        if (!$reservation) return $this->json(["message" => "Pas de réservation trouvée"], 404);
        if (!$reservation->isPaid())  return $this->json(["message" => "La réservation n'est pas payée"], 400);
        if (now() < $reservation->getProjectionEvent()->getDate()) return $this->json(["message" => "Une erreur s'est produite"], 400);
        if ($reservation->hasRate()) return $this->json(["message" => "Vous avez déjà donné une note"], 400);
        
        // START TRANSACTION
        $this->em->beginTransaction();
        try {
            // 1 CREATE COMMENT
            $newComment = (new Comment())
            ->setBody($comment)
            ->setRate($points);
            
            // 2 UPDATE MOVIE
            $movie
            ->setNotesTotalPoints($movie->getNotesTotalPoints() + $points)
            ->setNoteTotalVotes($movie->getNoteTotalVotes() + 1)
            ->addComment($newComment);
            
            // 3 UPDATE USER
            $userFromReservation
            ->addComment($newComment);
            
            // 4 UPDATE RESERVATION
            $reservation->setHasRate(true);
            
            // PERSIST & FLUSH UPDATED DATA
            $this->em->persist($newComment);
            $this->em->persist($movie);
            $this->em->persist($reservation);
            $this->em->persist($userFromReservation);
            $this->em->flush();

            // COMMIT TRANSACTION
            $this->em->commit();
            return $this->json(["message" => "OK"]);

        } catch (\Throwable $th) {
            $this->em->rollback();
            return $this->json([
                'content' => $content
            ]);
        }

        
    }
}
