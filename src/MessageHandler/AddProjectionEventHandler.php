<?php

namespace App\MessageHandler;

use App\Entity\Movie;
use App\Entity\MovieTheater;
use App\Entity\ProjectionEvent;
use App\Enum\ProjectionEventLanguage;
use App\Message\AddProjectionEventMessage;
use App\Repository\ReservationRepository;
use App\Repository\MovieRepository;
use App\Repository\MovieTheaterRepository;
use App\Repository\ProjectionFormatRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function Symfony\Component\Clock\now;

#[AsMessageHandler]
final class AddProjectionEventHandler
{

    public function __construct(
        private EntityManagerInterface $em,
        private MovieRepository $movieRepository,
        private MovieTheaterRepository $movieTheaterRepository,
        private ProjectionFormatRepository $projectionFormatRepository
        ) {}

    public function __invoke(AddProjectionEventMessage $message): void
    {
        dump('ADD NEW PROJECTION_EVENTS FOR ONE MOVIE AND FOR EACH THEATER INTO ROOM 1 AND 2');
        /** @var Movie $movie */
        $movie = $this->movieRepository->findAll()[0];
        $duration = $movie->getDurationInMinutes();
        $movieTheaters = $this->movieTheaterRepository->findAll();
        $projectionFormats = $this->projectionFormatRepository->findAll();
        foreach ($movieTheaters as $key => $movieTheater) {
            
            /** @var MovieTheater $movieTheater */
            $projectionRooms = $movieTheater->getProjectionRooms();
            $beginAt = (new \DateTime("9:30", new DateTimeZone("Europe/Paris")))->modify("+6 days");

            for ($i=0; $i<=3; $i++) { // On rajoute 8 séances par cinéma
                $projectionEventVF = (new ProjectionEvent())
                ->setMovie($movie)
                ->setFormat($projectionFormats[1])
                ->setBeginAt($beginAt)
                ->setProjectionRoom($projectionRooms[0])
                ->setLanguage(ProjectionEventLanguage::VF);
                $projectionEventVO = (new ProjectionEvent())
                ->setMovie($movie)
                ->setFormat($projectionFormats[1])
                ->setBeginAt($beginAt)
                ->setProjectionRoom($projectionRooms[1])
                ->setLanguage(ProjectionEventLanguage::VO);
                
                $movie->addProjectionEvent($projectionEventVF);
                $movie->addProjectionEvent($projectionEventVO);
                $this->em->persist($projectionEventVF);
                $this->em->persist($projectionEventVO);
                $this->em->persist($movie);
                $this->em->flush();
                $beginAt->modify("+{$duration} minutes");
                $beginAt->modify("+15 minutes"); // espacer les séances de 15 minutes
            }
        }
    }

    }

