FROM php:8.5.3-fpm-alpine

RUN apk update
RUN apk add curl git

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/backend

CMD ["php-fpm"]
