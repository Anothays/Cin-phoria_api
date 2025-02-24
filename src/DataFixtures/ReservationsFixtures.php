<?php

namespace App\DataFixtures;

use App\Entity\ProjectionEvent;
use App\Entity\Reservation;
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
            ->addSeat($seat);

            if ($i >= 4) $reservation->setCreatedAt(new \DateTimeImmutable('yesterday')); // Make some unpaid reservations outdated for testing
            $manager->persist($reservation);
            $manager->flush();
        }
    }


}