FROM php:8.4-apache

RUN apt-get update \
     && apt-get install -y git zip libzip-dev libicu-dev libjpeg-dev libpng-dev  \
    && apt-get clean

RUN docker-php-ext-configure intl
RUN docker-php-ext-install -j$(nproc) iconv pdo_mysql zip opcache intl gd

# Set the workdir
WORKDIR /var/www/html

#Install composer
COPY --from=composer:2.9 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Start the container
CMD composer install --no-interaction && \
    (bin/console doctrine:migrations:migrate --no-interaction -v || true) && \
    apache2-foreground
