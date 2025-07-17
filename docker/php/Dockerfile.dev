FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libsodium-dev \
    unzip \
    git \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    sodium

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

# Copier les fichiers de configuration d'abord
COPY composer.json composer.lock symfony.lock ./

# Installer les dépendances sans scripts
RUN composer install --no-scripts --no-autoloader

# Copier tout le reste du code
COPY . .
RUN composer require hwi/oauth-bundle --no-scripts

# Générer l'autoloader et exécuter les scripts maintenant que tout est copié
RUN composer dump-autoload --optimize && composer run-script post-install-cmd

# Assurer les bonnes permissions pour var/
RUN chown -R www-data:www-data /var/www/html/var && \
    chmod -R 755 /var/www/html/var && \
    chown -R www-data:www-data /var/www/html/config && \
    chmod -R 755 /var/www/html/config