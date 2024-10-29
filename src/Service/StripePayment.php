<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StripePayment 
{

  private $stripeClient;

  public function __construct( readonly private string $clientSecret, readonly private string $webhookSecret, private EntityManagerInterface $em) 
  {
    Stripe::setApiKey($this->clientSecret);
    $this->stripeClient = new StripeClient($clientSecret);

  }



  public function fulfill_checkout($session_id) {
  
    // TODO: Log the string "Fulfilling Checkout Session $session_id"
  
    // TODO: Make this function safe to run multiple times,
    // even concurrently, with the same session ID
  
    // TODO: Make sure fulfillment hasn't already been
    // peformed for this Checkout Session
  
    // Retrieve the Checkout Session from the API with line_items expanded
    try {
      $checkout_session = $this->stripeClient->checkout->sessions->retrieve($session_id);
      $reservationId = $checkout_session->metadata->reservation;
      if ($checkout_session->payment_status != 'unpaid') {
        $reservation =  $this->em->getRepository(Reservation::class)->find($reservationId);
        if (!$reservation) throw new NotFoundHttpException('Reservation not found');
        $reservation->setPaid(true);
        $items = $checkout_session->allLineItems($session_id);
        foreach ($items->data as $item) {
          $ticketCategory = $this->em->getRepository(TicketCategory::class)->findOneBy([
            "categoryName" => $item->description
          ]);
          if (!$ticketCategory) throw new NotFoundHttpException('Ticket category not found');
          for($i=1; $i <= $item->quantity; $i++) {
            $ticket = (new Ticket())
            ->setCategory($ticketCategory);
            $reservation->addTicket($ticket);
            $this->em->persist($ticket);
          }
        }
        $this->em->persist($reservation);
        $this->em->flush();
        // TODO: Record/save fulfillment status for this
        // Checkout Session
        return true;
      }
    } catch (\Throwable $th) {
      return false;
    }
  

  } 


}