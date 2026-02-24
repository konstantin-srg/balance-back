FROM php:8.5.3-fpm-alpine

RUN apk update
RUN apk add curl git

RUN docker-php-ext-install pdo pdo_mysql php-curl

WORKDIR /var/www/backend

COPY --from=composer:latest-bin /composer /usr/local/bin/composer
RUN composer install

CMD ["php-fpm"]
