<?php

namespace App\Service\Ticket;

use App\Constant\ErrorMessages;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use App\Exception\TicketCreationException;
use Doctrine\ORM\EntityManagerInterface;

class TicketManager
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Create tickets in SQL database using Doctrine
     */
    public function createSqlTickets(Reservation $reservation, array $categories): array
    {
        if ($reservation->getSeats()->count() !== count($categories)) throw new TicketCreationException(ErrorMessages::TICKETS_COUNT_DOES_NOT_MATCH_RESERVED_SEATS_COUNT);
        
        $tickets = [];
        /** @var TicketCategory[] $allTicketcategories */
        $allTicketcategories = $this->em->getRepository(TicketCategory::class)->findAll();
        foreach ($categories as $categoryName) {
            /** @var TicketCategory $currentCategory */
            $currentCategory = $this->em->getRepository(TicketCategory::class)->findOneBy(['categoryName' => $categoryName]);
            $ticket = (new Ticket())
                ->setReservation($reservation)
                ->setCategory($currentCategory)
            ;
            $this->em->persist($ticket);
            $tickets[] = $ticket;
        }

        // Make reservation paid
        $reservation->setPaid(true);
        $this->em->persist($reservation);
        
        $this->em->flush();
        return $tickets;
    }
} 