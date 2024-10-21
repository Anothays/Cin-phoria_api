#!/bin/bash

php bin/console lexik:jwt:generate-keypair --overwrite;
echo 'GENERATE KEYPAIR';

php bin/console cache:warmup --env=prod;
echo 'WARMUP';

apache2-foreground;
echo 'APACHE FOREGROUND';

exit 0