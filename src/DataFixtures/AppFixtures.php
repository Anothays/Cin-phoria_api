<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Entity\MovieCategory;
use App\Entity\MovieTheater;
use App\Entity\ProjectionEvent;
use App\Entity\ProjectionFormat;
use App\Entity\ProjectionRoom;
use App\Entity\ProjectionRoomSeat;
use App\Entity\Reservation;
use App\Entity\TicketCategory;
use App\Entity\User;
use App\Entity\UserStaff;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Enum\ProjectionEventLanguage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager)
    {
        // Support loading fixtures size
        ini_set('memory_limit', '256M'); 

        // CREATE STAFF USERS
        $users_staff_data = json_decode(file_get_contents(__DIR__ . '/users_staff.json'), true);
        $usersStaff = [];
        foreach ($users_staff_data as $value) {
            $userStaff = (new UserStaff())
                ->setEmail($value['email'])
                ->setRoles($value['roles'])
                ->setFirstname($value['firstname'])
                ->setLastname($value['lastname'])
                ;
            $userStaff->setPassword($this->passwordHasher->hashPassword($userStaff, $value['password']));
            $usersStaff[] = $userStaff;
            $manager->persist($userStaff);
        }

        // CREATE USERS
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
        
        $manager->flush();

        // CREATE MOVIE CATEGORIES
        $movie_categories_data = json_decode(file_get_contents(__DIR__ . '/movie_categories.json'), true);        
        $movieCategories = [];
        foreach ($movie_categories_data as $value) {
            $movie_category = new MovieCategory();
            $movie_category->setCategoryName($value["categoryName"]);
            $manager->persist(($movie_category));
            $movieCategories[$value["categoryName"]] = $movie_category;
        }

        // CREATE MOVIES
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
                // ->setNoteTotalVotes($value["noteTotalVotes"])
                // ->setNotesTotalPoints($value["notesTotalPoints"])
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

         // CREATE COMMENTS
        $comments_data = json_decode(file_get_contents(__DIR__ . '/comments.json'), true);
        $comments = [];
        foreach ($comments_data as $value) {
        // update votes and notes on movies
        $movie = $movies[$value['movie']];
        if (isset($value['rate'])) {
            $movie
            ->setNoteTotalVotes($movie->getNoteTotalVotes() + 1)
            ->setNotesTotalPoints($movie->getNotesTotalPoints() + $value["rate"]);
        }
        $comment = (new Comment())
        ->setBody($value['body'])
        ->setVerified(true)
        ->setRate($value['rate'] ?? null)
        ->setMovie($movie)
        ->setUser($users[$value['user']]);
        $comments[] = $comment;
        $manager->persist($comment);
        $manager->persist($movie);
        }

        $manager->flush();

        // CREATE MOVIE THEATERS WITH PROJECTION ROOM AND SEATS
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
                        $manager->persist($projectionRoomSeat);
                    }
                    $row++;
                }
                $manager->persist($projectionRoom);
                $projectionRooms[$movieTheater->getTheaterName()][] = $projectionRoom;
            }
            $movieTheaters['theater_name'] = $movieTheater;
            $manager->persist($movieTheater);
        }

        $manager->flush();

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
        
        
        // CREATE PROJECTION EVENTS
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

        $manager->flush();

        // CREATE TICKET CATEGORIES
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
        //     ->setUpdatedAt((new \DateTimeImmutable())->add((new \DateInterval("PT{$key}M"))))
        //     ->setUser($users[0])
        //     ->setProjectionEvent($projectionEvents[0])
        //     ->addSeat($projectionEvents[0]->getProjectionRoom()->getProjectionRoomSeats()[$key]);
        //     // $ticket = (new Ticket())->setCategory($ticket_categories[array_rand($ticket_categories)]);
        //     // $reservation->addTicket($ticket);
        //     // $manager->persist($ticket);
        //     $manager->persist($reservation);
        //     $reservations[] = $reservation;
        // }




        // Make projectionEvent in the past
        $projectionEventOver = (new ProjectionEvent())
        ->setFormat($projectionFormats["STANDARD"])
        ->setLanguage((ProjectionEventLanguage::VF))
        ->setMovie($movies["Le Comte de Monte-Cristo"])
        ->setProjectionRoom($projectionRooms["Disney village"][2])
        ->setBeginAt((new Datetime())->modify("-7 day"));
        $manager->persist($projectionEventOver);

        // Make reservation in the past
        $reservationOver = (new Reservation())
        ->setPaid(true)
        ->setProjectionEvent($projectionEventOver)
        ->setUser($users[0]);
        $manager->persist($reservationOver);




        // $tickets_data = json_decode(file_get_contents(__DIR__ . '/tickets.json'), true);
        // $tickets = [];
        // foreach ($tickets_data as $key => $value) {}

        $manager->flush();

        
    }

}