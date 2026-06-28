FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y unzip git \
    && docker-php-ext-install simplexml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/logs public/uploads \
    && chmod -R 777 storage public/uploads

EXPOSE 10000

CMD php -S 0.0.0.0:${PORT:-10000} -t public
