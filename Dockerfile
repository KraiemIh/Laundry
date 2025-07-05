FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim unzip git curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    supervisor \
    nginx \
    mysql-client

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage

# Copy nginx config
COPY ./nginx/render.conf /etc/nginx/sites-available/default

CMD service nginx start && php-fpm
