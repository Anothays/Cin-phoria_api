<?php

namespace App\Service;

use App\Document\Ticket as DocumentTicket;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

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

  public function fulfill_checkout(string $session_id): bool
{
    $conn = $this->em->getConnection();

    try {
        
        // Retrieve Stripe checkout session
        $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
        if ($checkout_session->payment_status === 'unpaid') {
          throw new \Exception('Erreur de paiement');
        }

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
          
          // dd($checkout_session, $reservationId, $items, $categories, $ticketsJson);
        return true;

    } catch (\Throwable $th) {
      // dd($th);
        // Log de l'erreur et retour d'un échec
        error_log($th->getMessage());
        return false;
    }
}


  // public function fulfill_checkout($session_id) {
  
  //   $conn = $this->em->getConnection();  // Récupère la connexion DBAL
  //   $conn->beginTransaction();
  //   try {

  //     $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
  //     $reservationId = $checkout_session->metadata->reservation;

  //     if ($checkout_session->payment_status != 'unpaid') {
  //       $reservation = $conn->fetchAssociative(
  //         'SELECT * FROM reservation WHERE id = :id FOR UPDATE',
  //         ['id' => $reservationId]
  //       );

  //       if (!$reservation) throw new NotFoundHttpException('Reservation not found');

  //       $conn->executeStatement(
  //         'UPDATE reservation SET is_paid = :paid WHERE id = :id',
  //         ['paid' => true, 'id' => $reservationId]
  //       );

  //       $items = $checkout_session->allLineItems($session_id);

  //       foreach ($items->data as $key => $item) {

  //         $ticketCategory = $conn->fetchAssociative(
  //           'SELECT * FROM ticket_category WHERE category_name = :categoryName',
  //           ['categoryName' => $item->description]
  //         );

  //         if (!$ticketCategory) throw new NotFoundHttpException('Ticket category not found');
  //         $date = (new \DateTime())->format('Y-m-d H:i:s');
  //         for ($i = 1; $i <= $item->quantity; $i++) {
  //           $conn->insert('ticket', [
  //               'category_id' => $ticketCategory['id'],
  //               'reservation_id' => $reservationId,
  //               'unique_code' => Uuid::v4(),
  //               'created_at' => $date,
  //               'updated_at' => $date,
  //               'is_scanned' => 0,
  //           ]);

  //           try {
  //             $movieTitle = $conn->fetchAssociative(
  //               "SELECT m.title FROM movie m
  //               JOIN projection_event pe ON pe.movie_id = m.id
  //               JOIN reservation r ON r.projection_event_id = pe.id
  //               WHERE r.id = :reservationId
  //               FOR UPDATE",
  //               ["reservationId" => $reservationId]
  //             );
  //             $documentTicket = new DocumentTicket($movieTitle['title'], $ticketCategory['category_name'], $ticketCategory['price']);
  //             $this->dm->persist($documentTicket);
  //           } catch (\Throwable $th) {
  //             return;
  //           }
  //         }
  //       }
        
  //       $conn->commit(); 
  //       $this->dm->flush();
  //       return true;
  //     } else {
  //       throw new Exception('Erreur de paiement');
  //     }
  //   } catch (\Throwable $th) {
  //     $conn->rollBack();
  //     return false;
  //   }
  

  // } 


}