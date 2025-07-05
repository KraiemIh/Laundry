FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP (sans les dev)
RUN composer install --no-dev --optimize-autoloader

# Permissions Laravel sur le dossier storage
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose le port dynamique de Render (ou 8000 par défaut)
EXPOSE ${PORT:-8000}

# Lancer le serveur Laravel avec le port Render
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
