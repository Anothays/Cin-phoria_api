<?php

namespace App\Service;

use App\Document\Ticket as TicketDoc;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\Ticket\TicketManager;
use App\Exception\StripePaymentException;
use App\Exception\TicketCreationException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripePayment 
{
    private $stripeClient;
    private array $mongoTicketsToCreate = [];

    public function __construct(
        private ParameterBagInterface $params, 
        private EntityManagerInterface $em,
        private DocumentManager $dm,
        private TicketManager $ticketManager,
        private ReservationRepository $reservationRepository
    ) {
        $clientSecret = $this->params->get('stripe_secret_key');
        Stripe::setApiKey($clientSecret);
        $this->stripeClient = new StripeClient($clientSecret);
    }

    /**
     * Process checkout completion and create tickets
     * @throws StripePaymentException
     * @throws TicketCreationException
     */
    public function fulfillCheckout(string $sessionId): bool
    {
        $this->em->beginTransaction();
        
        try {
            $checkoutSession = $this->validateStripeSession($sessionId);
            $categories = $this->extractTicketCategories($checkoutSession);
            $reservation = $this->getReservation($checkoutSession->metadata->reservation);

            // Create SQL tickets
            $sqlTickets = $this->ticketManager->createSqlTickets($reservation, $categories);

            // Prepare and persist MongoDB documents
            // $this->prepareMongoDbTickets($sqlTickets, $reservation);
            // $this->persistMongoDbTickets();

            // If everything is successful, commit SQL transaction
            $this->em->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            
            throw new StripePaymentException(
                "Failed to process checkout: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate Stripe session and payment status
     * @throws StripePaymentException
     */
    protected function validateStripeSession(string $sessionId): StripeSession
    {
        $session = $this->stripeClient->checkout->sessions->retrieve($sessionId);
        if ($session->payment_status === 'unpaid') {
            throw new StripePaymentException('Payment status is unpaid');
        }
        return $session;
    }

    /**
     * Extract ticket categories from Stripe session
     */
    protected function extractTicketCategories(StripeSession $session): array
    {
        $items = $session->allLineItems($session->id);
        $categories = [];
        
        foreach ($items->data as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $categories[] = mb_convert_encoding(
                    $item->description,
                    'UTF-8',
                    'auto'
                );
            }
        }
        
        return $categories;
    }

    /**
     * Get reservation by ID
     * @throws TicketCreationException
     */
    protected function getReservation(string $reservationId): Reservation
    {
        $reservation = $this->reservationRepository->find($reservationId);
        if (!$reservation) {
            throw new TicketCreationException("Reservation not found");
        }
        return $reservation;
    }

    /**
     * Prepare MongoDB documents without persisting them
     */
    private function prepareMongoDbTickets(array $sqlTickets, Reservation $reservation): void
    {
        $projectionEvent = $reservation->getProjectionEvent();
        $extraCharge = $projectionEvent->getFormat()->getExtraCharge() ?? 0;
        $movieTitle = $projectionEvent->getMovie()->getTitle();

        foreach ($sqlTickets as $ticket) {
            $this->mongoTicketsToCreate[] = new TicketDoc(
                $movieTitle,
                $ticket->getCategory()->getCategoryName(),
                $ticket->getCategory()->getPrice() + $extraCharge
            );
        }
    }

    /**
     * Persist prepared MongoDB tickets
     */
    private function persistMongoDbTickets(): void
    {
        foreach ($this->mongoTicketsToCreate as $ticketDoc) {
            $this->dm->persist($ticketDoc);
        }
        $this->dm->flush();
        $this->mongoTicketsToCreate = []; // Reset the array
    }
}



