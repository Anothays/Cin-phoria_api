<?php

namespace App\DataFixtures;

use App\Entity\ProjectionEvent;
use App\Entity\Reservation;
use App\Entity\Ticket;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationsFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array
    {
        return ['reservations'];
    }

    public function load(ObjectManager $manager)
    {
        // PENDING RESERVATIONS
        for ($i=1; $i<=6; $i++) {
            
            $user = $manager->createQuery("select u from App\Entity\User u")->setMaxResults(1)->setMaxResults(1)->getResult()[0];
            $query = $manager->createQuery("select p from App\Entity\ProjectionEvent p ")->setMaxResults(1);
            
            /** @var ProjectionEvent $projectionEvent  */
            $projectionEvent = $query->getResult()[0]; // Take first projectionEvent (no matter which one)
            $seat = $projectionEvent->getAvailableSeats()->first();
            
            $reservation = (new Reservation())
            ->setUser($user)
            ->setProjectionEvent($projectionEvent)
            ->setHasRate(false)
            ->setPaid(false)
            ->addSeat($seat)
            ;

            if ($i >= 4) $reservation->setCreatedAt(new \DateTimeImmutable('yesterday')); // Make some unpaid reservations outdated for testing
            $manager->persist($reservation);
            $manager->flush();
        }
        
        // TERMINATED SUCESSFUL RESERVATIONS
        if ($_ENV['APP_ENV'] !== 'test') { // Create ticket into test environment will throw error because mongodb is not configured in test environnement currently
            for ($i=1; $i<=3; $i++) {
                
                $user = $manager->createQuery("select u from App\Entity\User u where u.email = 'john@doe.com' ")->getOneOrNullResult();
                $query = $manager->createQuery("select p from App\Entity\ProjectionEvent p ")->setMaxResults(1);
                $result = $query->getResult();
                /** @var ProjectionEvent $projectionEvent  */
                $projectionEvent = $result[0];
                
                $ticketCategory = $manager->createQuery("select c from App\Entity\TicketCategory c where c.categoryName = 'Tarif Normal' ")->getResult()[0];
                
                $ticket1 = (new Ticket())->setCategory($ticketCategory);
                $ticket2 = (new Ticket())->setCategory($ticketCategory);
                
                $reservation = (new Reservation())
                ->setUser($user)
                ->setProjectionEvent($projectionEvent)
                ->setHasRate(false)
                ->setPaid(true)
                ->addSeat($projectionEvent->getAvailableSeats()->first())
                ->addSeat($projectionEvent->getAvailableSeats()->get(1))
                ->addTicket($ticket1)
                ->addTicket($ticket2);
                $manager->persist($ticket1);
                $manager->persist($ticket2);
                $manager->persist($reservation);
                $manager->flush();
            }
        }
    }


}