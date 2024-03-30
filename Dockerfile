FROM php:latest

RUN apt update -y && apt upgrade -y

RUN apt install -y \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        imagemagick \
        libmagickwand-dev imagemagick \
        git \
        zip unzip \
        openssl libssl-dev libcurl4-openssl-dev \
        protobuf-compiler \
        autoconf zlib1g-dev \
    && pecl install imagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install redis && docker-php-ext-enable redis
RUN docker-php-ext-install opcache && docker-php-ext-enable opcache
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install sockets && docker-php-ext-enable sockets

RUN pecl install --configureoptions 'enable-sockets="yes" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="yes" enable-swoole-curl="yes" enable-cares="no"' swoole
RUN echo 'extension=swoole.so' >> /usr/local/etc/php/conf.d/swoole.ini

RUN pecl install xlswriter

RUN pecl install grpc
RUN echo 'extension=grpc.so' >> /usr/local/etc/php/conf.d/grpc.ini

RUN pecl install protobuf
RUN echo "extension=protobuf.so" >> /usr/local/etc/php/conf.d/protobuf.ini

RUN docker-php-ext-configure pcntl --enable-pcntl && docker-php-ext-install pcntl

RUN pecl install apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN rm -rf /var/cache/apt/lists


COPY build/php/php.ini /usr/local/etc/php/php.ini
