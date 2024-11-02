/*

Ce fichier crée la base de données et ses tables.
Aucune donnée n'est insérée.

*/



CREATE DATABASE IF NOT EXISTS cinephoria;
USE cinephoria;

-- Table des utilisateurs
CREATE TABLE `user` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(60),
  `lastname` VARCHAR(60),
  `email` VARCHAR(180),
  `password` VARCHAR(255),
  `roles` JSON NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  `is_verified` TINYINT(1) NOT NULL,
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) DEFAULT CHARSET=utf8mb4;

-- Table des films
CREATE TABLE `movie` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `director` VARCHAR(50) NOT NULL,
  `synopsis` LONGTEXT NOT NULL,
  `casting` LONGTEXT,
  `released_on` DATETIME NOT NULL,
  `posters` LONGTEXT NOT NULL,
  `minimum_age` INT NOT NULL,
  `staff_favorite` TINYINT(1) DEFAULT NULL,
  `notes_total_points` INT DEFAULT NULL,
  `note_total_votes` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  `duration_in_minutes` INT NOT NULL,
  `cover_image_name` VARCHAR(255) DEFAULT NULL,
  `cover_image_size` INT DEFAULT NULL
) DEFAULT CHARSET=utf8mb4;

-- Table des catégories de films
CREATE TABLE `movie_category` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(60) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME DEFAULT NOW()
) DEFAULT CHARSET=utf8mb4;

-- Table des tickets
CREATE TABLE `ticket_category` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(60) NOT NULL,
  `price` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW()
) DEFAULT CHARSET=utf8mb4;

-- Table des salles de cinéma
CREATE TABLE `movie_theater` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `theater_name` VARCHAR(60) NOT NULL,
  `city` VARCHAR(60) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW()
) DEFAULT CHARSET=utf8mb4;

-- Table des formats de projection
CREATE TABLE `projection_format` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `projection_format_name` VARCHAR(30) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME DEFAULT NOW(),
  `extra_charge` INT DEFAULT NULL
) DEFAULT CHARSET=utf8mb4;

-- Table des salles de projection
CREATE TABLE `projection_room` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `movie_theater_id` INT NOT NULL,
  `title_room` VARCHAR(2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME DEFAULT NOW(),
  UNIQUE KEY `unique_ProjectionRoom` (`title_room`, `movie_theater_id`),
  FOREIGN KEY (`movie_theater_id`) REFERENCES `movie_theater` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table des événements de projection
CREATE TABLE `projection_event` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `format_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  `projection_room_id` INT NOT NULL,
  `language` VARCHAR(255) NOT NULL,
  `begin_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME DEFAULT NOW(),
  FOREIGN KEY (`movie_id`) REFERENCES `movie` (`id`),
  FOREIGN KEY (`projection_room_id`) REFERENCES `projection_room` (`id`),
  FOREIGN KEY (`format_id`) REFERENCES `projection_format` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table des commentaires
CREATE TABLE `comment` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `movie_id` INT DEFAULT NULL,
  `body` LONGTEXT NOT NULL,
  `rate` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  `is_verified` TINYINT(1) NOT NULL,
  FOREIGN KEY (`movie_id`) REFERENCES `movie` (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table des réservations
CREATE TABLE `reservation` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `projection_event_id` INT DEFAULT NULL,
  `is_paid` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  `has_rate` TINYINT(1) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  FOREIGN KEY (`projection_event_id`) REFERENCES `projection_event` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table des sièges dans les salles de projection
CREATE TABLE `projection_room_seat` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `projection_room_id` INT NOT NULL,
  `seat_row` VARCHAR(1) NOT NULL,
  `seat_number` INT NOT NULL,
  `is_for_reduced_mobility` TINYINT(1) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME DEFAULT NOW(),
  UNIQUE KEY `unique_seatRow_seatNumber_ProjectionRoom` (`seat_row`, `seat_number`, `projection_room_id`),
  FOREIGN KEY (`projection_room_id`) REFERENCES `projection_room` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table de réservation des sièges
CREATE TABLE `reservation_projection_room_seat` (
  `reservation_id` INT NOT NULL,
  `projection_room_seat_id` INT NOT NULL,
  PRIMARY KEY (`reservation_id`, `projection_room_seat_id`),
  FOREIGN KEY (`projection_room_seat_id`) REFERENCES `projection_room_seat` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

-- Table des demandes de réinitialisation de mot de passe
CREATE TABLE `reset_password_request` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `selector` VARCHAR(20) NOT NULL,
  `hashed_token` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) DEFAULT CHARSET=utf8mb4;

-- Table des tickets
CREATE TABLE `ticket` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `reservation_id` INT DEFAULT NULL,
  `category_id` INT NOT NULL,
  `unique_code` VARCHAR(36) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  FOREIGN KEY (`category_id`) REFERENCES `ticket_category` (`id`),
  FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`)
);

-- Table des utilisateurs staff
CREATE TABLE `user_staff` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(60) NOT NULL,
  `lastname` VARCHAR(60) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `roles` JSON NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT NOW(),
  `updated_at` DATETIME NOT NULL DEFAULT NOW(),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) DEFAULT CHARSET=utf8mb4;

-- Table d'association entre catégories de films et films
CREATE TABLE `movie_category_movie` (
  `movie_category_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  PRIMARY KEY (`movie_category_id`, `movie_id`),
  FOREIGN KEY (`movie_category_id`) REFERENCES `movie_category` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movie` (`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4;

