FROM php:8.2-fpm

RUN apt-get update  \
    && apt-get install -y git zip libzip-dev libicu-dev \
    && apt-get clean

RUN pecl install xdebug
RUN docker-php-ext-configure intl
RUN docker-php-ext-install -j$(nproc) iconv pdo_mysql zip opcache intl

#Set the workdir
WORKDIR /var/www/html

#Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN mv /usr/local/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
RUN mkdir "/.composer" && chmod 777 /.composer
ENV COMPOSER_ALLOW_SUPERUSER 1

# Enable logging
ENV PHPFPM_CATCH_WORKERS_OUTPUT yes

# Healthcheck
HEALTHCHECK NONE

#Start container
CMD /usr/local/sbin/php-fpm -F
