FROM alpine:latest AS prepare-stage

ARG ENVIRONMENT_NAME
ARG BRANCH_NAME

ENV BRANCH_NAME=${BRANCH_NAME}

WORKDIR /app

RUN apk update && apk add --no-cache git

RUN /bin/sh -c "git clone --single-branch --branch $BRANCH_NAME https://github.com/igorproc/vrum-backend.git ."

COPY .. .

FROM php:8.1-cli

ARG ENVIRONMENT_NAME

ENV ENVIRONMENT_NAME=${ENVIRONMENT_NAME}

WORKDIR /app

RUN apt-get update && \
    apt-get install -y \
    libicu-dev libsodium-dev libzip-dev zip unzip git && \
    apt-get clean

RUN docker-php-ext-install pdo_mysql intl sodium zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --from=prepare-stage /app /app

RUN /bin/sh -c "cp ./environments/.env.${ENVIRONMENT_NAME} .env"

RUN composer install

RUN /bin/sh ./docker/script/prepare.sh

# EXPOSE 8000

CMD ["php", "artisan", "serve"]
