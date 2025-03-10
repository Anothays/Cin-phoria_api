<?php

namespace App\Service;

use App\Document\Ticket as TicketDoc;
use App\Entity\Reservation;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripePayment 
{

  private $stripeClient;

  public function __construct(
    readonly private string $clientSecret, 
    readonly private string $webhookSecret, 
    private EntityManagerInterface $em,
    private DocumentManager $dm,
  ) 
  {
    Stripe::setApiKey($this->clientSecret);
    $this->stripeClient = new StripeClient($clientSecret);
  }

  public function fulfillCheckout(string $session_id): bool
  {
    $conn = $this->em->getConnection();
    try {

      // RETRIEVE STRIPE CHECKOUT SESSION
      $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
      if ($checkout_session->payment_status === 'unpaid') throw new \Exception('Erreur de paiement');
      
      // GET ALL ITEMS FROM STRIPE CHECKOUT SESSION IN ORDER TO CREATE NEW TICKETS
      $items = $checkout_session->allLineItems($session_id);
      
      // GET RESERVATIONID
      $reservationId = $checkout_session->metadata->reservation;
      
      /** 
       *  CREATE ARRAY : 
       *  ["Moins de 14 ans", "Etudiant scolaire", "Tarif Normal"]
       *  AS PARAMETER FOR SQL SCRIPT /sql/transaction.sql
      */
      $categories = [];
      foreach ($items->data as $item) {
        for ($i=0; $i<$item->quantity; $i++ ) {
          $categories[] = mb_convert_encoding($item->description, 'UTF-8', 'auto');
        }
      }
      
      // EXECUTE SQL TRANSACTION ==> PERSIST TICKETS INTO SQL DATABASE
      $conn->executeStatement(
        file_get_contents(__DIR__ . '/../../' .'/sql/transaction.sql'),
        [ 'reservation_id' => $reservationId, 'tickets' => json_encode($categories, JSON_UNESCAPED_UNICODE)]
      );

      // HANDLE TICKET PERSISTENCE INTO MONGODB
      $reservationRepository = $this->em->getRepository(Reservation::class);
      /** @var Reservation $reservation */
      $reservation = $reservationRepository->find($reservationId);
      $projectionEvent = $reservation->getProjectionEvent();
      $projectionEventFormat = $projectionEvent->getFormat();
      $projectionEventFormat->getExtraCharge();
      $movieTitle = $reservation->getProjectionEvent()->getMovie()->getTitle();
      $tickets = $reservation->getTickets();
      foreach ($tickets as $ticket) { 
        /** @var Ticket $ticket */
        $ticketDoc = new TicketDoc(
          $movieTitle, 
          $ticket->getCategory()->getCategoryName(), 
          $ticket->getCategory()->getPrice() + ($projectionEventFormat->getExtraCharge() ?? 0),
        );
        $this->dm->persist($ticketDoc);
        $this->dm->flush();
      }
      
      return true;

    } catch (\Throwable $th) {
        error_log($th->getMessage());
        return false;
    }
  }

}



