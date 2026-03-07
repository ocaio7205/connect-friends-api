FROM php:8.2-apache

RUN a2dismod mpm_event
RUN a2enmod mpm_prefork

RUN docker-php-ext-install mysqli pdo_mysql

COPY . /var/www/html/

EXPOSE 80
