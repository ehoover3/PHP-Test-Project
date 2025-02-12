FROM php:8.0-apache

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY .env /var/www/html/.env
ENV $(cat /var/www/html/.env | xargs)
RUN chown www-data:www-data /var/www/html/.env