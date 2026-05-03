FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    curl \
    libxml2-dev \
    unzip \
  && docker-php-ext-install xml \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html