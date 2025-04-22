FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    librdkafka-dev \
    git \
    unzip \
    && docker-php-ext-install pdo_pgsql \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копирование исходного кода (с учетом .dockerignore)
COPY . .
RUN cp .env.example .env

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear

EXPOSE 8000