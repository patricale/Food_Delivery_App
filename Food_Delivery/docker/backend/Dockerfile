FROM php:8.2-apache

# Installazione driver necessari per MySQL (PDO)
RUN docker-php-ext-install pdo pdo_mysql

# Abilitazione mod_rewrite per Apache
RUN a2enmod rewrite

WORKDIR /var/www/html