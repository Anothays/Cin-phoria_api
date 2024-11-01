<?php

namespace App\Service;

use App\Entity\Reservation;
use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Twig\Environment;

class PdfMaker
{

  public function __construct(
    private Environment $twig,
    private DompdfFactoryInterface $factory,
    private DompdfWrapperInterface $wrapper,
  ){}
  
  public function makeTicketsPdfFile(Reservation $reservation)
  {
    $html = $this->twig->render('pdf/pinted_tickets.html.twig', ['resa' => $reservation ]);
    try {
      $pdf = $this->factory->create();
      $pdf->loadHtml($html);
      $pdf->setPaper('A6');
      $pdf->render();
      return $pdf->output();
    } catch (\Throwable $th) {
      return false;
    }
  }


  public function makeTicketsPdfFile2(Reservation $reservation)
  {
    $html = $this->twig->render('pdf/pinted_tickets.html.twig', ['resa' => $reservation ]);
    $filename = 'lol.pdf';
    return $this->wrapper->getStreamResponse($html, $filename, [
      'defaultPaperSize' => \Dompdf\Adapter\CPDF::$PAPER_SIZES['a6']
    ]);
  }

 
}