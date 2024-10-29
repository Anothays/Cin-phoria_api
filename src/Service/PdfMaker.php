<?php

namespace App\Service;

use App\Entity\Reservation;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class PdfMaker
{

  public function __construct(
    private Pdf $pdf,
    private Environment $twig,
  ){}

  public function makeTicketsPdfFile(Reservation $reservation)
  {
    $html = $this->twig->render('pdf/tickets.html.twig', ['resa' => $reservation ]);
    $options = [
      'page-size' => 'A6',
      'enable-local-file-access' => true,
    ];

    try {
      $pdf = $this->pdf->getOutputFromHtml($html, $options);
      return $pdf;
    } catch (\Throwable $th) {
      return false;
    }
  }

}