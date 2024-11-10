<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\ProjectionEvent;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Enum\ProjectionEventLanguage;
use App\Repository\MovieCategoryRepository;
use App\Repository\ProjectionFormatRepository;
use App\Repository\ProjectionRoomRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationsFixtures extends Fixture implements FixtureGroupInterface
{

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public static function getGroups(): array
    {
        return ['reservations'];
    }

    public function load(ObjectManager $manager)
    {

        for ($i=1; $i<=3; $i++) {
            
            $user = $this->em->createQuery("select u from App\Entity\User u where u.email = 'john@doe.com' ")->getOneOrNullResult();
            // $query = $this->em->createNativeQuery(
            //     "SELECT * FROM projection_event LIMIT 1;",
            //     new \Doctrine\ORM\Query\ResultSetMapping()
            // );
            $query = $this->em->createQuery("select p from App\Entity\ProjectionEvent p ")->setMaxResults(1);
            /** @var ProjectionEvent $projectionEvent  */
            $projectionEvent = $query->getResult()[0];
            dump($projectionEvent);
            $ticketCategory = $this->em->createQuery("select c from App\Entity\TicketCategory c where c.categoryName = 'Tarif Normal' ")->getResult()[0];
            dump($ticketCategory);

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
        }
        
        $manager->flush();
    }


}