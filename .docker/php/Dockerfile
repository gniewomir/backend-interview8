FROM php:8.1-fpm

RUN mkdir -p /etc/php/8.1/fpm/conf.d \
&&  mkdir -p /etc/php/8.1/cli/conf.d

COPY 99-extra.ini /etc/php/8.1/fpm/conf.d
COPY 99-extra.ini /etc/php/8.1/cli/conf.d

RUN apt update && apt install -y git zip libonig-dev librabbitmq-dev libxml2-dev && apt clean
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install mbstring mysqli xml pdo_mysql && pecl install amqp xdebug && docker-php-ext-enable amqp xdebug
