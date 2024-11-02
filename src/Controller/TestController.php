<?php

namespace App\Controller;


use App\Document\Ticket;
use App\Repository\ReservationRepository;
use App\Service\EmailSender;
use App\Service\PdfMaker;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;


class TestController extends AbstractController
{

    public function __construct(
        private Environment $twig,
        private EmailSender $emailSender,
    ) {}

    #[Route('/test', name: 'app_test')]
    public function index(DocumentManager $dm)
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

    #[Route('/test2', name: 'app_test2')]
    public function index2(PdfMaker $pdfMaker, ReservationRepository $reservationRepository): Response
    {   $resa = $reservationRepository->find(1);
        $attachent = $pdfMaker->makeTicketsPdfFile($resa);
        $email = $this->emailSender->makeAndSendEmail(
            "jeremy.snnk@gmail.com",
            'TEST',
            "email/email_tickets.html.twig",
            [ 'resa' => $resa ],
            $attachent ?? null,
        );

        return new Response($attachent, 200, [
            "Content-type" => 'application/pdf'
        ]);
    }

    #[Route('/test3', name: 'app_test3')]
    public function index3(PdfMaker $pdfMaker, ReservationRepository $reservationRepository): Response
    {
        $lol = unserialize(file_get_contents('lol.txt'));
        dd($lol);
    }
}
