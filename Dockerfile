FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zlib1g-dev \
    unzip \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN mkdir -p /var/www/logs \
    && chown -R www-data:www-data /var/www/logs \
    && chmod -R 775 /var/www/logs \
