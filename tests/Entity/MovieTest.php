<?php

namespace App\Tests\Entity;

use App\Entity\Movie;
use App\Entity\MovieTheater;
use App\Entity\ProjectionEvent;
use App\Entity\ProjectionRoom;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MovieTest extends KernelTestCase
{
    private $movie;
    private $movieTheater1;
    private $movieTheater2;
    private $projectionRoom1;
    private $projectionRoom2;

    public function setUp(): void
    {
        parent::setUp();
        
        // Création du film
        $this->movie = new Movie();
        $this->movie->setTitle('Test Movie');
        
        // Création des cinémas
        $this->movieTheater1 = new MovieTheater();
        $this->movieTheater1->setTheaterName('Cinéma 1')
                           ->setCity('Paris');
        
        $this->movieTheater2 = new MovieTheater();
        $this->movieTheater2->setTheaterName('Cinéma 2')
                           ->setCity('Lyon');
        
        // Création des salles
        $this->projectionRoom1 = new ProjectionRoom();
        $this->projectionRoom1->setMovieTheater($this->movieTheater1);
        
        $this->projectionRoom2 = new ProjectionRoom();
        $this->projectionRoom2->setMovieTheater($this->movieTheater2);
    }

    public function testGetProjectionEventsSortedByDateAndGroupedByTheater()
    {
        // Création des dates de projection
        $date1 = new DateTime('tomorrow');
        $date2 = new DateTime('tomorrow +1 day');
        
        // Création des événements de projection
        $event1 = new ProjectionEvent();
        $event1->setMovie($this->movie)
               ->setProjectionRoom($this->projectionRoom1)
               ->setBeginAt($date1);
        
        $event2 = new ProjectionEvent();
        $event2->setMovie($this->movie)
               ->setProjectionRoom($this->projectionRoom1)
               ->setBeginAt($date2);
        
        $event3 = new ProjectionEvent();
        $event3->setMovie($this->movie)
               ->setProjectionRoom($this->projectionRoom2)
               ->setBeginAt($date1);
        
        // Ajout des événements au film
        $this->movie->addProjectionEvent($event1);
        $this->movie->addProjectionEvent($event2);
        $this->movie->addProjectionEvent($event3);
        
        // Récupération du résultat
        $result = $this->movie->getProjectionEventsSortedByDateAndGroupedByTheater();
        
        // Vérifications
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // 2 dates différentes
        
        $date1Key = $date1->format('Y-m-d');
        $date2Key = $date2->format('Y-m-d');
        
        // Vérification de la première date
        $this->assertArrayHasKey($date1Key, $result);
        $this->assertCount(2, $result[$date1Key]); // 2 cinémas pour la première date
        
        // Vérification du premier cinéma pour la première date
        $this->assertArrayHasKey('Cinéma 1', $result[$date1Key]);
        $this->assertCount(1, $result[$date1Key]['Cinéma 1']['projectionEvents']);
        
        // Vérification du deuxième cinéma pour la première date
        $this->assertArrayHasKey('Cinéma 2', $result[$date1Key]);
        $this->assertCount(1, $result[$date1Key]['Cinéma 2']['projectionEvents']);
        
        // Vérification de la deuxième date
        $this->assertArrayHasKey($date2Key, $result);
        $this->assertCount(1, $result[$date2Key]); // 1 cinéma pour la deuxième date
        
        // Vérification du cinéma pour la deuxième date
        $this->assertArrayHasKey('Cinéma 1', $result[$date2Key]);
        $this->assertCount(1, $result[$date2Key]['Cinéma 1']['projectionEvents']);
        
        // Vérification des informations du cinéma
        $this->assertEquals([
            'id' => null,
            'theaterName' => 'Cinéma 1',
            'city' => 'Paris'
        ], $result[$date1Key]['Cinéma 1']['movieTheater']);
    }

    public function testGetProjectionEventsSortedByDateAndGroupedByTheaterWithPastEvents()
    {
        // Création d'un événement passé
        $pastDate = new DateTime('yesterday');
        $pastEvent = new ProjectionEvent();
        $pastEvent->setMovie($this->movie)
                 ->setProjectionRoom($this->projectionRoom1)
                 ->setBeginAt($pastDate);
        
        // Création d'un événement futur
        $futureDate = new DateTime('tomorrow');
        $futureEvent = new ProjectionEvent();
        $futureEvent->setMovie($this->movie)
                   ->setProjectionRoom($this->projectionRoom1)
                   ->setBeginAt($futureDate);
        
        // Ajout des événements au film
        $this->movie->addProjectionEvent($pastEvent);
        $this->movie->addProjectionEvent($futureEvent);
        
        // Récupération du résultat
        $result = $this->movie->getProjectionEventsSortedByDateAndGroupedByTheater();
        
        // Vérification que l'événement passé n'est pas inclus
        $pastDateKey = $pastDate->format('Y-m-d');
        $this->assertArrayNotHasKey($pastDateKey, $result);
        
        // Vérification que l'événement futur est inclus
        $futureDateKey = $futureDate->format('Y-m-d');
        $this->assertArrayHasKey($futureDateKey, $result);
        $this->assertCount(1, $result[$futureDateKey]['Cinéma 1']['projectionEvents']);
    }
} 