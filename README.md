# STUDI - ECF fin 2024 - Cinéphoria

## Lien vers le site déployé

https://cinephoria.jeremysnnk.ovh/

## Pour se connecter en tant qu'admin

- Email => studi_cinephoria_admin@jeremysnnk.ovh
- Mot de passe => changeMe

## Pour se connecter en tant qu'employé

- Email => studi_cinephoria_staff@jeremysnnk.ovh
- Mot de passe => changeMe

## Pour démarrer l'api en local (avec docker)

- cloner le projet sur votre machine
  ```bash
  git clone https://github.com/Anothays/Cin-phoria_api.git
  ```
- supprimer les extensions .dist aux fichiers .env.dist, .env.database.dist, .env.mongodb.dist, et définir chacune des variables d'environnement. Des commentaires sont présents devant chaque variable pour vous guider.
- Lancer le réseaux docker

  ```bash
  docker compose up -d
  ```

## Pour démarrer l'api en local (sans docker).

## Pré-requis :

- Une base de donnée mysql
- Une base de donnée mongodb
- l'extension mongodb de php

- supprimer l'extension .dist au fichier .env.dist (vous pouvez supprimer les fichiers .env.database.dist et .env.mongodb.dist) et définir chacune des variables d'environnement. Des commentaires sont présents devant chaque variable pour vous guider.

- adaptez les variables d'environnement DATABASE_URL et MONGODB_URL selon votre configuration en local.

- installer les dépendances
  ```bash
  composer install
  ```
- Création de la base de données avec les fixtures
  ```bash
  symfony console doctrine:database:reset -df
  ```

Si vous n'avez pas de base de donnée mysql ou mongodb sur votre machine hôte, vous pouvez lancer le réseau docker avec seulement les services mysql et mongodb. Pour cela vous devez d'abord décommenter les ports 3306 et 27017 dans le fichier compose.yaml.
Dans le cas, vous aurez besoin des fichiers .env.database, .env.mongodb utilisé par docker compose.
Ensuite démarrer les services avec la commande

```bash
 docker compose up -d database mongodb
```

## Concernant le paiement avec Stripe

Si vous tentez un paiement en local avec votre compte Stripe, il vous faudra activer la redirection vers votre webhook en local.

Pour cela il vous faut installer le client Stripe CLI : https://docs.stripe.com/stripe-cli

Après l'avoir installé lancez l'écoute de votre webhook

```bash
stripe listen --forward-to localhost:90/payment-webhook
```

(docker expose le port 90)

OU

```bash
stripe listen --forward-to localhost:8000/payment-webhook
```

## Technologies utilisées

- languages => PHP 8.2
- Framework => Symfony 7.1
- Système de gestion de base de données => Mysql et Mongodb
- ORM => doctrine
- moteur de template HTML => Twig
- gestionnaires de dépendance => composer
- server web => Apache2
