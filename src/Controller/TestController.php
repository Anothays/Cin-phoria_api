<?php

namespace App\Controller;


use App\Document\Ticket;
use App\Entity\Reservation as EntityReservation;
use App\Repository\ReservationRepository;
use App\Repository\TicketRepository;
use App\Service\EmailSender;
use App\Service\PdfMaker;
use Doctrine\ODM\MongoDB\DocumentManager;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Snappy\Pdf;
use Twig\Environment;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;


class TestController extends AbstractController
{

    public function __construct(

    ) {}

    #[Route('/test', name: 'app_test')]
    public function index(DocumentManager $dm): Response
    {
        $today = new \DateTime();
        $tickets = $dm->createQueryBuilder(Ticket::class)
        ->field('isPaid')->equals(true)
        ->field('createdAt')->gte($today->modify('-1 days'))
        ->sort('movieTitle', 'ASC')
        ->getQuery()
        ->execute()
        ;

        $ticketCountByMovie = [];
        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket */ 
            $movieTitle = $ticket->movieTitle;
            if (!isset($reservationCountByMovie[$movieTitle])) {
                $reservationCountByMovie[$movieTitle] = 0;
            }
            $reservationCountByMovie[$movieTitle]++;
        }

        dd($tickets, $ticketCountByMovie);

    }
}
