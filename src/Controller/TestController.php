<?php

namespace App\Controller;

use App\Document\Product;
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
        $product = (new Product())
        ->setName("A Foo bar")
        ->setPrice(234);
        $dm->persist($product);
        $dm->flush();

        return new Response('Created product with id ' . $product->getId());
    }
}
