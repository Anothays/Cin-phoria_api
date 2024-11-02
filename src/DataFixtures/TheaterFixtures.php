<?php

namespace App\DataFixtures;

use App\Entity\MovieCategory;
use App\Entity\MovieTheater;
use App\Entity\ProjectionFormat;
use App\Entity\ProjectionRoom;
use App\Entity\ProjectionRoomSeat;
use App\Entity\TicketCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TheaterFixtures extends Fixture implements FixtureGroupInterface
{
    public const PROJECTION_ROOMS = 'projection_rooms';

    public static function getGroups(): array
    {
        return ['theaters'];
    }

    public function load(ObjectManager $manager)
    {
        // CREATE MOVIE THEATERS WITH PROJECTION ROOM AND SEATS
        $movie_theaters_data = json_decode(file_get_contents(__DIR__ . '/movie_theaters.json'), true);
        $movieTheaters = [];
        $projectionRooms = [];
        foreach ($movie_theaters_data as $key => $value) {
            $movieTheater = (new MovieTheater())
                ->setTheaterName($value["theater_name"])
                ->setCity($value['city']);
            $projectionRooms[$movieTheater->getTheaterName()] = [];
            // Create 10 projection rooms.
            for($i=1; $i<=10; $i++) {
                $projectionRoom = (new ProjectionRoom())
                ->setTitleRoom("$i");
                $movieTheater->addProjectionRoom($projectionRoom);
                // Create 100 seats
                $row = 'A';
                for($x=1; $x<=10; $x++) {
                    for($y=1; $y<=10; $y++) {
                        $projectionRoomSeat = (new ProjectionRoomSeat())
                        ->setProjectionRoom($projectionRoom)
                        ->setSeatRow($row)
                        ->setSeatNumber($y);
                        $projectionRoom->addProjectionRoomSeat($projectionRoomSeat);
                        // make top center seats reserved for mobility reduced persons
                        if ($x === 1 && $y >= 6 && $y <= 15) {
                            $projectionRoomSeat->setForReducedMobility(true);
                        } else {
                            $projectionRoomSeat->setForReducedMobility(false);
                        }
                        $manager->persist($projectionRoomSeat);
                    }
                    $row++;
                }
                $manager->persist($projectionRoom);
                $projectionRooms[$movieTheater->getTheaterName()][] = $projectionRoom;
            }
            $movieTheaters['theater_name'] = $movieTheater;
            // $this->addReference(self::PROJECTION_ROOMS, $projectionRooms);
            $manager->persist($movieTheater);
        }

        // CREATE TICKET CATEGORIES
        $ticket_categories_data = json_decode(file_get_contents(__DIR__ . '/ticket_categories.json'), true);
        $ticket_categories = [];
        foreach ($ticket_categories_data as $value) {
            $ticketCategory = (new TicketCategory())
            ->setCategoryName($value["category_name"])
            ->setPrice($value['price']);
            $ticket_categories[$value["category_name"]] = $ticketCategory;
            $manager->persist($ticketCategory);
        }

        // CREATE PROJECTION FORMAT
        $projection_formats_data = json_decode(file_get_contents(__DIR__ . '/projection_formats.json'), true);
        $projectionFormats = [];
        foreach ($projection_formats_data as $value) {
            $projectionFormat = (new ProjectionFormat())
            ->setProjectionFormatName($value['projection_format_name'])
            ->setExtraCharge($value['extra_charge']);
            $manager->persist($projectionFormat);
            $projectionFormats[$value['projection_format_name']] = $projectionFormat;
        }

        // CREATE MOVIE CATEGORIES
        $movie_categories_data = json_decode(file_get_contents(__DIR__ . '/movie_categories.json'), true);        
        $movieCategories = [];
        foreach ($movie_categories_data as $value) {
            $movie_category = new MovieCategory();
            $movie_category->setCategoryName($value["categoryName"]);
            $manager->persist(($movie_category));
            $movieCategories[$value["categoryName"]] = $movie_category;
        }

        $manager->flush();
    }

}