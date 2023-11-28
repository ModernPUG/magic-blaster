FROM php:8.2-cli-alpine

RUN apk update && apk upgrade \
    && apk add --no-cache curl

RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /game

CMD ["php", "bin/ws.php"]
