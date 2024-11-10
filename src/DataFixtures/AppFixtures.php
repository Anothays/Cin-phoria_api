<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\TheaterFixtures;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Support loading fixtures size
        ini_set('memory_limit', '256M'); 
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TheaterFixtures::class, // MovieTheater + Rooms + Seats + Ticket categories + Movie categories + Projection formats
            MovieFixtures::class, // Movie + ProjectionEvents
            ReservationsFixtures::class // Reservation + Tickets
        ];
    }
}