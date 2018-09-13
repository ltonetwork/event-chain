FROM legalthings/apache-php71
  
ADD . /app
WORKDIR /app

RUN apt-get update
RUN apt-get install -y git

ENV APACHE_DOCUMENT_ROOT /var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN composer install --no-dev
