FROM php:5.5-fpm

RUN docker-php-ext-install mysqli;
