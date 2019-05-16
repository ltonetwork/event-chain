FROM legalthings/fpm-php:7.2
  
ADD . /app
WORKDIR /app

RUN apt-get update
RUN apt-get install -y git

RUN composer install --no-dev
