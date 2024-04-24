# BUILD PHP
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

RUN composer install --no-interaction --no-dev --no-progress --no-scripts
RUN composer dump-autoload --no-dev --classmap-authoritative
RUN composer dump-env prod
RUN php bin/console assets:install public
RUN php bin/console asset-map:compile

# BUILD IMAGE
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y libicu-dev zip libzip-dev wait-for-it && apt-get clean

RUN pecl install redis
RUN docker-php-ext-install -j$(nproc) pdo_mysql intl opcache zip bcmath
RUN docker-php-ext-enable redis

WORKDIR /var/www/html

COPY ./docker/phpfpm/prod/conf.d/php.ini /usr/local/etc/php/conf.d/99-php.ini
COPY ./docker/phpfpm/prod/conf.d/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

RUN useradd -u 1010 -ms /bin/sh appuser
RUN mkdir -p var/log var/cache/prod && \
    chown -R appuser: var/

COPY --from=builder /var/www/html/bin/ /var/www/html/bin/
COPY --from=builder /var/www/html/config/ /var/www/html/config/
COPY --from=builder /var/www/html/migrations/ /var/www/html/migrations/
COPY --from=builder /var/www/html/public/ /var/www/html/public/
COPY --from=builder /var/www/html/src/ /var/www/html/src/
COPY --from=builder /var/www/html/templates/ /var/www/html/templates/
COPY --from=builder /var/www/html/translations/ /var/www/html/translations/
COPY --from=builder /var/www/html/vendor/ /var/www/html/vendor/
COPY --from=builder /var/www/html/.env.local.php /var/www/html/
COPY --from=builder /var/www/html/composer.json /var/www/html/

HEALTHCHECK NONE

EXPOSE 80

RUN chsh -s /bin/sh appuser
