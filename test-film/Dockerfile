FROM php:8.1-apache

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip
 

WORKDIR /var/www/


RUN mkdir -p var/cache/dev && chown -R www-data:www-data var/cache/dev
RUN mkdir -p var/log && chown -R www-data:www-data var/log


COPY . .


EXPOSE 80


CMD ["apache2-foreground"]

