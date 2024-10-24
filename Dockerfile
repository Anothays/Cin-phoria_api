FROM php:8.2-apache

COPY . /var/www/html/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    libicu-dev \
    zip unzip \
    && docker-php-ext-install intl mysqli pdo pdo_mysql

RUN a2enmod rewrite

RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN composer install --optimize-autoloader --no-interaction

RUN chmod +x ./shell/init.sh ./shell/resetdb.sh

CMD ["bash", "./shell/init.sh"]

EXPOSE 90


