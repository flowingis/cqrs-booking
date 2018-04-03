FROM php:7.2-fpm-alpine

RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql
RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /var/www