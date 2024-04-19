FROM alpine:latest AS prepare-stage

ARG ENVIRONMENT_NAME
ARG BRANCH_NAME

ENV BRANCH_NAME=${BRANCH_NAME}

WORKDIR /app

RUN apk update && apk add --no-cache git

RUN /bin/sh -c "git clone --single-branch --branch $BRANCH_NAME https://github.com/igorproc/vrum-backend.git ."

COPY .. .

FROM php:8.1-apache

RUN apt-get update && \
    apt-get install -y \
    libicu-dev libsodium-dev

RUN docker-php-ext-install pdo_mysql intl sodium

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

COPY --from=prepare-stage /app /app

WORKDIR /app

RUN /bin/sh -c "cp ./environments/.env.${ENVIRONMENT_NAME} .env"

COPY .. .

RUN chown -R www-data:www-data /var/www/html/storage

RUN chown -R www-data:www-data /var/www/html/bootstrap/cache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install

COPY ./docker/apache/.${ENVIRONMENT_NAME}.conf /etc/apache2/sites-available/000-default.conf

RUN /bin/sh ./docker/script/prepare.sh

RUN a2enmod rewrite

EXPOSE 3000

CMD ["apache2-foreground"]
