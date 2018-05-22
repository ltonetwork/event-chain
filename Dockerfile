FROM legalthings/apache-php
  
ADD . /app
WORKDIR /app

RUN apt-get install -y libsodium-dev
RUN pecl install libsodium-1.0.6 && \
    echo "extension=libsodium.so" > /usr/local/etc/php/conf.d/ext-sodium.ini

ENV APACHE_DOCUMENT_ROOT /var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN composer install --no-dev
