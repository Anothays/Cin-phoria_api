<?php

namespace App\Service;

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
      // Retrieve Stripe checkout session
      $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
      if ($checkout_session->payment_status === 'unpaid') throw new \Exception('Erreur de paiement');
      
      // Get all items from Stripe checkout session in order to create new tickets
      $items = $checkout_session->allLineItems($session_id);
      
      // Get reservationId
      $reservationId = $checkout_session->metadata->reservation;
      
      /** 
       *  Create array like this 
       *  ["Moins de 14 ans","Etudiant scolaire","Etudiant scolaire","Tarif Normal","Tarif Normal","Tarif Normal"]
       *  as parameter for SQL script ==>  /sql/transaction.sql
      */
      $categories = [];
      foreach ($items->data as $item) {
        for ($i=0; $i<$item->quantity; $i++ ) {
          $categories[] = mb_convert_encoding($item->description, 'UTF-8', 'auto');
        }
      }
      $ticketsJson = json_encode($categories, JSON_UNESCAPED_UNICODE);
      
      // Execute SQL transaction
      $conn->executeStatement(
        file_get_contents(__DIR__ . '/../../' .'/sql/transaction.sql'),
        [
          'reservation_id' => $reservationId,
          'tickets' => $ticketsJson,
        ]
      );
        
      return true;

    } catch (\Throwable $th) {
      // dd($th);
        // Log de l'erreur et retour d'un échec
        error_log($th->getMessage());
        return false;
    }
  }

}