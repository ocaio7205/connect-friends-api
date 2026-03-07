FROM php:8.2-apache

RUN a2dismod mpm_event
RUN a2denmod mpm_event

RUN docker-php-ext-install mysqli pdo_mysql

COPY . /var/www/html/

EXPOSE 80
