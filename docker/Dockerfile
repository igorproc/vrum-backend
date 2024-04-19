FROM alpine:latest AS prepare-stage

ARG ENVIRONMENT_NAME
ARG BRANCH_NAME

ENV BRANCH_NAME=${BRANCH_NAME}

WORKDIR /app

RUN apk update && apk add --no-cache git

RUN /bin/sh -c "git clone --single-branch --branch $BRANCH_NAME https://github.com/igorproc/vrum-backend.git ."

COPY .. .

FROM php:8.1-fpm

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

RUN apt-get update && \
    apt-get install -y \
    libicu-dev libsodium-dev libzip-dev zip unzip git && \
    apt-get clean

RUN docker-php-ext-install pdo_mysql intl sodium zip

COPY --from=prepare-stage /app /app

WORKDIR /app

RUN /bin/sh -c "cp ./environments/.env.${ENVIRONMENT_NAME} .env"

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install

RUN /bin/sh ./docker/script/prepare.sh

RUN groupadd -g 1000 app
RUN useradd -u 1000 -ms /bin/bash -g app app

EXPOSE 3000

CMD ["php-fpm"]
