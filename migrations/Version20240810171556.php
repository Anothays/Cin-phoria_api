<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240810171556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE movie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(100) NOT NULL, director VARCHAR(50) NOT NULL, synopsis CLOB NOT NULL, casting CLOB DEFAULT NULL --(DC2Type:array)
        , duration TIME NOT NULL, released_on DATETIME NOT NULL, posters CLOB NOT NULL --(DC2Type:array)
        , minimum_age INTEGER NOT NULL, is_staff_favorite BOOLEAN DEFAULT NULL, notes_total_points INTEGER DEFAULT NULL, note_total_votes INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE movie_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_name VARCHAR(60) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE movie_category_movie (movie_category_id INTEGER NOT NULL, movie_id INTEGER NOT NULL, PRIMARY KEY(movie_category_id, movie_id), CONSTRAINT FK_D60B290E3DC01115 FOREIGN KEY (movie_category_id) REFERENCES movie_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D60B290E8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D60B290E3DC01115 ON movie_category_movie (movie_category_id)');
        $this->addSql('CREATE INDEX IDX_D60B290E8F93B6FC ON movie_category_movie (movie_id)');
        $this->addSql('CREATE TABLE movie_theater (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, theater_name VARCHAR(60) NOT NULL, city VARCHAR(60) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_category');
        $this->addSql('DROP TABLE movie_category_movie');
        $this->addSql('DROP TABLE movie_theater');
    }
}
