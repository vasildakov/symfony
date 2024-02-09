FROM php:8.3-fpm

# Install PDO and PGSQL Drivers
RUN apt-get update && apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Install zip
RUN apt-get install -y libzip-dev zip \
  && docker-php-ext-install zip

# Install intl
RUN apt-get -y update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

## Install redis
RUN pecl install redis && docker-php-ext-enable redis

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

## Install Composer
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer