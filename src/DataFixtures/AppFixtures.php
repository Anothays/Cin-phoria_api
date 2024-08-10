<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\MovieCategory;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
  
        $movie_categories = json_decode(file_get_contents(__DIR__ . '/movie_categories.json'), true);
        $moviesData = json_decode(file_get_contents(__DIR__ . '/movies.json'), true);
        
        // Create MovieCategories
        $categories = [];
        foreach ($movie_categories as $value) {
            $movie_category = new MovieCategory();
            $movie_category->setCategoryName($value["categoryName"]);
            $manager->persist(($movie_category));
            $categories[$value["categoryName"]] = $movie_category;
        }

        foreach ($moviesData as $value) {
            $movie = new Movie();
            $movie
                ->setTitle($value["title"])
                ->setDirector($value["director"])
                ->setDuration(new DateTime($value["duration"]))
                ->setMinimumAge($value["minimumAge"])
                ->setSynopsis($value["synopsis"])
                ->setStaffFavorite($value["isStaffFavorite"])
                ->setReleasedOn(new DateTime($value["releasedOn"]))
                ->setNoteTotalVotes($value["noteTotalVotes"])
                ->setNotesTotalPoints($value["notesTotalPoints"])
                ->setPosters([])
                ->setCasting($value["casting"]);
             
            foreach ($value['movieCategories'] as $categoryName) {
                if (isset($categories[$categoryName])) {
                    $movie->addMovieCategory($categories[$categoryName]);
                }
            }
            $manager->persist($movie);
        }

        $manager->flush();

        
    }
}