FROM php:8.2-apache

WORKDIR /var/www/html/

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
    
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    a2enmod rewrite && \
    chmod +x ./shell/init.sh ./shell/resetdb.sh

RUN apt-get update && apt-get install -y \
    libicu-dev \
    zip unzip \
    libxrender1 \
    libxext6 \
    libfontconfig1 \
    xz-utils \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-install intl mysqli pdo pdo_mysql 

RUN pecl install mongodb \
&& docker-php-ext-enable mongodb

CMD ["bash", "./shell/init.sh"]

EXPOSE 90

