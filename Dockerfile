FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
        libssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libzip-dev \
        zlib1g-dev \
        libicu-dev \
        g++ \
        libonig-dev \
        libbase58-dev \
        locales \
        locales-all \
        git
RUN docker-php-ext-install -j$(nproc) mbstring
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install gettext
RUN docker-php-ext-install zip
RUN docker-php-ext-install bcmath
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN apt-get install -y libyaml-dev && pecl install yaml && docker-php-ext-enable yaml
RUN pecl install base58-0.1.4 && docker-php-ext-enable base58
RUN apt-get install -y libmariadb-dev && docker-php-ext-install pdo_mysql

RUN locale-gen

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /app && rm -fr /var/www/html && ln -s /app /var/www/html

ADD . /app
WORKDIR /app

RUN composer install --no-dev
