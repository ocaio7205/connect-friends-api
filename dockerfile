FROM php:8.2-apache

# Garante apenas o MPM prefork
RUN a2dismod mpm_event || true \
 && a2dismod mpm_worker || true \
 && a2enmod mpm_prefork

# Extensões do PHP
RUN docker-php-ext-install mysqli pdo_mysql

# Copia o projeto
COPY . /var/www/html/

# Porta do Apache
EXPOSE 80