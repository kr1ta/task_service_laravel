# Базовый образ PHP
FROM php:8.2-fpm

# Установка необходимых расширений
RUN apt-get update && apt-get install -y \
    libpq-dev \
    librdkafka-dev \
    git \
    unzip \
    && docker-php-ext-install pdo_pgsql \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копирование исходного кода (с учетом .dockerignore)
WORKDIR /var/www/html
COPY . .

# Установка зависимостей
RUN composer install --no-dev --optimize-autoloader

# Публикация конфигураций Laravel
RUN php artisan config:cache
RUN php artisan route:cache

# Открытие порта
EXPOSE 8000