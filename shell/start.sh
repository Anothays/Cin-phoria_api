#!/bin/bash

CONTAINER_FIRST_STARTUP="CONTAINER_FIRST_STARTUP"

if [ ! -e /$CONTAINER_FIRST_STARTUP ]; then

    touch /$CONTAINER_FIRST_STARTUP

    ./shell/wait-for-it.sh --timeout=0 database:3306 
    echo 'WAIT FOR IT DONE';

    composer install --optimize-autoloader --no-dev --no-interaction;
    echo 'COMPOSER INSTALL OK';

    php bin/console d:d:r -df
    echo "DATABASE RESET DONE",

    # php bin/console d:s:u -f
    # echo "DATABASE UPDATED";

    # php bin/console d:f:l -nq
    # echo "RESET DATABASE DONE";

    php bin/console cache:warmup --env=prod;
    echo 'WARMUP';

    chmod -R 775 /var/www/html/var/cache /var/www/html/var/log;
    chown -R www-data:www-data /var/www/html/var/cache /var/www/html/var/log;
    echo 'CHMOD DONE';
fi

php bin/console lexik:jwt:generate-keypair -nq --overwrite;
echo 'GENERATE KEYPAIR';

php bin/console messenger:consume & 
echo 'STARTS SCHEDULER WITH MESSENGER';

apache2-foreground

