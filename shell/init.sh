#!/bin/bash

# Vérifier si le répertoire vendor existe
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies...";
    composer install --optimize-autoloader --no-dev --no-interaction;
    echo 'COMPOSER INSTALL OK';
else
    echo "Dépendance déjà installées, SKIP COMPOSER INSTALL.";
fi

# php bin/console doctrine:database:create || echo "Database already exists."
# echo 'CREATE DATABASE';

# php bin/console doctrine:schema:create
# echo 'CREATE SCHEMA';

php bin/console cache:warmup --env=prod;
echo 'WARMUP';

chmod -R 775 /var/www/html/var/cache /var/www/html/var/log;

chown -R www-data:www-data /var/www/html/var/cache /var/www/html/var/log;

php bin/console lexik:jwt:generate-keypair --overwrite;
echo 'GENERATE KEYPAIR';

apache2-foreground;
echo 'APACHE FOREGROUND';

exit 0;