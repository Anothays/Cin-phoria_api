<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\ProjectionEvent;
use App\Enum\ProjectionEventLanguage;
use App\Repository\MovieCategoryRepository;
use App\Repository\ProjectionFormatRepository;
use App\Repository\ProjectionRoomRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture implements FixtureGroupInterface
{

    public function __construct(
        private MovieCategoryRepository $movieCategoryRepository,
        private ProjectionFormatRepository $projectionFormatRepository,
        private ProjectionRoomRepository $projectionRoomRepository,
        private EntityManagerInterface $em,
    ) {}

    public static function getGroups(): array
    {
        return ['movies'];
    }

    public function load(ObjectManager $manager)
    {

        // CREATE MOVIES
        $movies_data = json_decode(file_get_contents(__DIR__ . '/movies.json'), true);
        $movies = [];
        $destinationDir = "public/uploads/images/";
        if (!is_dir($destinationDir)) mkdir($destinationDir, 0777, true);
        foreach ($movies_data as $key => $value) {
            copy("src/DataFixtures/medias/movies_posters/{$value['imageCover']}", "{$destinationDir}{$value['imageCover']}");
            $movie = (new Movie())
                ->setTitle($value["title"])
                ->setDirector($value["director"])
                ->setDurationInMinutes($value["durationInMinutes"])
                ->setMinimumAge($value["minimumAge"])
                ->setSynopsis($value["synopsis"])
                ->setStaffFavorite($value["staffFavorite"])
                ->setReleasedOn(new \DateTime($value["releasedOn"]))
                // ->setNoteTotalVotes($value["noteTotalVotes"])
                // ->setNotesTotalPoints($value["notesTotalPoints"])
                ->setPosters([])
                ->setCasting($value["casting"])
                ->setCoverImageName($value['imageCover']);
                
            if ($key + 1 > count($movies_data) / 2 ) {
                $movie->setCreatedAt($this->getLastWednesday()); // à partir de la seconde moitié des fixtures, rajouter les film au drnier mercredi
            } else {
                $movie->setCreatedAt($this->getLastWednesday()->modify("-7 days")); // Pour la première moitié les films sont rajoutés à l'avant-derner mercredi
            }
            
            foreach ($value['movieCategories'] as $categoryName) {
                $category = $this->movieCategoryRepository->findOneBy([ 'categoryName' => $categoryName ]);
                $movie->addMovieCategory($category);
            }

            $movies[$value['title']] = $movie;
            $manager->persist($movie);
        }
        
        // CREATE PROJECTION EVENTS
        $projection_events_data = json_decode(file_get_contents(__DIR__ . '/projection_events.json'), true);
        $projectionEvents = [];
        // $projectionRooms = $this->getReference(TheaterFixtures::PROJECTION_ROOMS);
        for ($i = 0; $i < 6; $i++) {
            foreach ($projection_events_data as $value) {
                $projectionFormat = $this->projectionFormatRepository->findOneBy([ 'projectionFormatName' => $value["format"] ]);
                $theater = $value["projectionRoom"]["movie_theater"];
                $titleRoom = $value["projectionRoom"]["salle"];
                $projectionRoom = $this->em->createQuery("SELECT r FROM APP\Entity\ProjectionRoom r JOIN r.movieTheater m WHERE m.theaterName = :theater AND r.titleRoom = :room")
                ->setParameters([':theater' => $theater, ':room' => $titleRoom])
                ->getOneOrNullResult();
                $projectionEvent = (new ProjectionEvent())
                ->setFormat($projectionFormat)
                ->setLanguage((ProjectionEventLanguage::from($value["language"])))
                ->setMovie($movies[$value["movie"]])
                ->setProjectionRoom($projectionRoom)
                ->setBeginAt((new \Datetime($value["begin_at"]))->modify("+{$i} day"));
                $manager->persist($projectionEvent);
                $projectionEvents[] = $projectionEvent;
            }
        }

        $manager->flush();
    }

    
    public function getLastWednesday(): \DateTime
    {
        $lastWednesday = new \DateTime();
        $dayOfWeek = (int) $lastWednesday->format('w'); // 0 (dimanche) à 6 (samedi)
        $daysToSubtract = ($dayOfWeek >= 3) ? $dayOfWeek - 3 : $dayOfWeek + 4;
        $lastWednesday->modify("-$daysToSubtract days");
        return $lastWednesday;
    }

}