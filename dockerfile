FROM php:8.2-apache

# Extensões do PHP
RUN docker-php-ext-install mysqli pdo_mysql

# Copia o projeto
COPY . /var/www/html/

# Porta do Apache
EXPOSE 80