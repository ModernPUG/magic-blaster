FROM php:8.2-cli-alpine

RUN docker-php-ext-configure pcntl --enable-pcntl \
  && docker-php-ext-install \
    pcntl

WORKDIR /game

CMD ["php", "bin/ws.php"]