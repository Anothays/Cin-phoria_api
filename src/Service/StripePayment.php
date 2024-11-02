<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class StripePayment 
{

  private $stripeClient;

  public function __construct( readonly private string $clientSecret, readonly private string $webhookSecret, private EntityManagerInterface $em) 
  {
    Stripe::setApiKey($this->clientSecret);
    $this->stripeClient = new StripeClient($clientSecret);

  }



  public function fulfill_checkout($session_id) {
  
    $conn = $this->em->getConnection();  // Récupère la connexion DBAL
    $conn->beginTransaction();
    try {

      $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
      $reservationId = $checkout_session->metadata->reservation;

      if ($checkout_session->payment_status != 'unpaid') {
        $reservation = $conn->fetchAssociative(
          'SELECT * FROM reservation WHERE id = :id FOR UPDATE',
          ['id' => $reservationId]
        );

        if (!$reservation) throw new NotFoundHttpException('Reservation not found');

        $conn->executeStatement(
          'UPDATE reservation SET is_paid = :paid WHERE id = :id',
          ['paid' => true, 'id' => $reservationId]
        );

        $items = $checkout_session->allLineItems($session_id);

        foreach ($items->data as $key => $item) {

          $ticketCategory = $conn->fetchAssociative(
            'SELECT * FROM ticket_category WHERE category_name = :categoryName',
            ['categoryName' => $item->description]
          );

          if (!$ticketCategory) throw new NotFoundHttpException('Ticket category not found');

          for ($i = 1; $i <= $item->quantity; $i++) {

            $conn->insert('ticket', [
                'category_id' => $ticketCategory['id'],
                'reservation_id' => $reservationId,
                'unique_code' => Uuid::v4()
            ]);
          }
        }
        
        $conn->commit(); 
        return true;
      } else {
        throw new Exception('Erreur de paiement');
      }
    } catch (\Throwable $th) {
      $conn->rollBack();
      return false;
    }
  

  } 


}