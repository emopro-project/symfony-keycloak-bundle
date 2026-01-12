############################
# STAGE 1 — DEPENDENCIES
############################
FROM composer:2.8.4 AS deps

WORKDIR /var/www/html

# Git safe directory (obligatoire dans Docker)
RUN git config --global --add safe.directory /var/www/html

# Copier uniquement les fichiers Composer pour profiter du cache Docker
COPY composer.json composer.lock ./

# Installer les dépendances PROD uniquement
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-sockets \
    --ignore-platform-req=ext-amqp

# Copier le reste de l'application
COPY . .



############################
# STAGE 2 — RUNTIME FINAL
############################
FROM php:8.2-apache AS final

WORKDIR /var/www/html

# Dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libpq-dev \
    librabbitmq-dev \
    libssl-dev \
    libsasl2-dev \
    libcurl4-openssl-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd sockets pdo_pgsql

# Extensions PECL nécessaires
RUN pecl install redis amqp \
    && docker-php-ext-enable redis amqp

# Apache config
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copier l'application + vendor depuis deps
COPY --from=deps /var/www/html /var/www/html

# Permissions Symfony
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/var \
    && a2enmod rewrite

USER www-data

EXPOSE 80
