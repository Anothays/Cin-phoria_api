<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\MovieCategory;
use App\Entity\MovieTheater;
use App\Entity\ProjectionEvent;
use App\Entity\ProjectionFormat;
use App\Entity\ProjectionRoom;
use App\Entity\ProjectionRoomSeat;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Enum\ProjectionEventLanguage;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function Symfony\Component\Clock\now;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager)
    {
        // Support loading fixtures size
        ini_set('memory_limit', '256M'); 

        // Create users
        $users_data = json_decode(file_get_contents(__DIR__ . '/users.json'), true);
        $users = [];
        foreach ($users_data as $value) {
            $user = (new User())
                ->setEmail($value['email'])
                ->setRoles($value['roles'])
                ->setFirstname($value['firstname'])
                ->setLastname($value['lastname'])
                ->setVerified(true)
                ;
            $user->setPassword($this->passwordHasher->hashPassword($user, $value['password']));
            $users[] = $user;
            $manager->persist($user);
        }

  
        // Create MovieCategories
        $movie_categories_data = json_decode(file_get_contents(__DIR__ . '/movie_categories.json'), true);        
        $movieCategories = [];
        foreach ($movie_categories_data as $value) {
            $movie_category = new MovieCategory();
            $movie_category->setCategoryName($value["categoryName"]);
            $manager->persist(($movie_category));
            $movieCategories[$value["categoryName"]] = $movie_category;
        }

        // Create Movies
        $movies_data = json_decode(file_get_contents(__DIR__ . '/movies.json'), true);
        // $imagesCover = array_diff(scandir("src/DataFixtures/medias"), ['.DS_Store', '.', '..']);
        $movies = [];
        $destinationDir = "public/uploads/images/";
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }
        foreach ($movies_data as $value) {
            copy("src/DataFixtures/medias/movies_posters/{$value['imageCover']}", "{$destinationDir}{$value['imageCover']}");
            $movie = (new Movie())
                ->setTitle($value["title"])
                ->setDirector($value["director"])
                ->setDurationInMinutes($value["durationInMinutes"])
                ->setMinimumAge($value["minimumAge"])
                ->setSynopsis($value["synopsis"])
                ->setStaffFavorite($value["staffFavorite"])
                ->setReleasedOn(new DateTime($value["releasedOn"]))
                ->setNoteTotalVotes($value["noteTotalVotes"])
                ->setNotesTotalPoints($value["notesTotalPoints"])
                ->setPosters([])
                ->setCasting($value["casting"])
                ->setCoverImageName($value['imageCover'])
                ;
            
            foreach ($value['movieCategories'] as $categoryName) {
                if (isset($movieCategories[$categoryName])) {
                    $movie->addMovieCategory($movieCategories[$categoryName]);
                }
            }
            $movies[$value['title']] = $movie;
            $manager->persist($movie);
        }

        // Create movietheaters with projection rooms and seats
        $movie_theaters_data = json_decode(file_get_contents(__DIR__ . '/movie_theaters.json'), true);
        $movieTheaters = [];
        $projectionRooms = [];
        foreach ($movie_theaters_data as $value) {
            $movieTheater = (new MovieTheater())
                ->setTheaterName($value["theater_name"])
                ->setCity($value['city']);
            $projectionRooms[$movieTheater->getTheaterName()] = [];
            // Create 10 projection rooms.
            for($i=1; $i<=10; $i++) {
                $projectionRoom = (new ProjectionRoom())
                ->setTitleRoom("$i");
                $movieTheater->addProjectionRoom($projectionRoom);
                // Create 200 seats
                $row = 'A';
                for($x=1; $x<=10; $x++) {
                    for($y=1; $y<=20; $y++) {
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
                    }
                    $row++;
                }
                
                $projectionRooms[$movieTheater->getTheaterName()][] = $projectionRoom;
            }
            $movieTheaters['theater_name'] = $movieTheater;
            $manager->persist($movieTheater);
        }

        // Create projection formats
        $projection_formats_data = json_decode(file_get_contents(__DIR__ . '/projection_formats.json'), true);
        $projectionFormats = [];
        foreach ($projection_formats_data as $value) {
            $projectionFormat = (new ProjectionFormat())
            ->setProjectionFormatName($value['projection_format_name'])
            ->setExtraCharge($value['extra_charge']);
            $manager->persist($projectionFormat);
            $projectionFormats[$value['projection_format_name']] = $projectionFormat;
        }
        
        // Create projection events
        $projection_events_data = json_decode(file_get_contents(__DIR__ . '/projection_events.json'), true);
        $projectionEvents = [];
        for ($i = 0; $i < 6; $i++) {
            foreach ($projection_events_data as $value) {
                $projectionEvent = (new ProjectionEvent())
                    ->setFormat($projectionFormats[$value["format"]])
                    ->setLanguage((ProjectionEventLanguage::from($value["language"])))
                    ->setMovie($movies[$value["movie"]])
                    ->setProjectionRoom($projectionRooms[$value["projectionRoom"]["movie_theater"]][$value["projectionRoom"]["salle"]])
                    ->setBeginAt((new Datetime($value["begin_at"]))->modify("+{$i} day"));
                $manager->persist($projectionEvent);
                $projectionEvents[] = $projectionEvent;
            }
        }

        // Create ticket categories
        $ticket_categories_data = json_decode(file_get_contents(__DIR__ . '/ticket_categories.json'), true);
        $ticket_categories = [];
        foreach ($ticket_categories_data as $key => $value) {
            $ticketCategory = (new TicketCategory())
            ->setCategoryName($value["category_name"])
            ->setPrice($value['price']);
            $ticket_categories[$value["category_name"]] = $ticketCategory;
            $manager->persist($ticketCategory);
        }

        // ATTENTION ===> CLASSE A FINIR (RELATION ETC)
        // Create reservations and tickets
        // $reservations_data = json_decode(file_get_contents(__DIR__ . '/reservations.json'), true);
        // $reservations = [];
        // foreach ($reservations_data as $key => $value) {
        //     $reservation = (new Reservation())
        //     ->setPaid($value['is_paid'])
        //     ->setUpdatedAt((new DateTimeImmutable())->add((new DateInterval("PT{$key}M"))))
        //     ->setUser($users[1])
        //     ->setProjectionEvent($projectionEvents[0])
        //     ->addSeat($projectionEvents[0]->getProjectionRoom()->getProjectionRoomSeats()[$key]);
        //     // $ticket = (new Ticket())->setCategory($ticket_categories[array_rand($ticket_categories)]);
        //     // $reservation->addTicket($ticket);
        //     // $manager->persist($ticket);
        //     $manager->persist($reservation);
        //     $reservations[] = $reservation;
        // }


        // $tickets_data = json_decode(file_get_contents(__DIR__ . '/tickets.json'), true);
        // $tickets = [];
        // foreach ($tickets_data as $key => $value) {}

        $manager->flush();

        
    }

}