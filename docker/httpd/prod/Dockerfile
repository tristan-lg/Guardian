# BUILD PHP & assets
FROM php:8.3-fpm as builder

RUN apt-get update && apt-get install -y git zip libicu-dev libzip-dev && apt-get clean

RUN docker-php-ext-install -j$(nproc) zip bcmath

WORKDIR /var/www/html
COPY ./app /var/www/html

ENV SYMFONY_ENV=prod
ENV APP_ENV=prod

#Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN mv /usr/local/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
RUN mkdir "/.composer" && chmod 777 /.composer
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer install --no-interaction --no-dev --no-progress
RUN php bin/console assets:install public
RUN php bin/console asset-map:compile


# BUILD
FROM httpd:2.4-alpine

COPY --from=builder /var/www/html/public/ /var/www/html/public/

COPY ./docker/httpd/prod/conf/httpd.conf /usr/local/apache2/conf/httpd.conf
COPY ./docker/httpd/prod/conf/vhost.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf

# Healthcheck
HEALTHCHECK NONE
