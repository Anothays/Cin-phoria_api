<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\TicketRepository;
use App\Service\EmailSender;
use App\Service\PdfMaker;
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
        private EmailSender $emailSender,
        private PdfMaker $pdfMaker,
    ) {}

    #[Route('/test', name: 'app_test')]
    public function index(ReservationRepository $reservationRepository): Response
    {

        $resa = $reservationRepository->find(2);

        $this->emailSender->makeAndSendEmail(
            "jeremy.snnk@gmail.com",
            'TEST BILLETS PJ PDF',
            'email/email_tickets.html.twig',
            [
                'resa' => $resa
            ],
            $this->pdfMaker->makeTicketsPdfFile($resa) ?? null,
            );

        return $this->render('pdf/tickets.html.twig', [
            'resa' => $resa
        ]);
    }
}
