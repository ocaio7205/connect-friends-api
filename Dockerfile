FROM php:8.2-apache

COPY . /var/www/html/

EXPOSE 10000

CMD ["apache2-foreground"]
