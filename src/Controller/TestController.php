<?php

namespace App\Controller;


use App\Document\Ticket;
use App\Entity\Reservation;
use App\Repository\MovieRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\EmailSender;
use App\Service\PdfMaker;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;


class TestController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em, 
    ) {}

    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        $reservationRepo = $this->em->getRepository(Reservation::class);
        return $this->json(['hello'=>'lol']);
    }
}
