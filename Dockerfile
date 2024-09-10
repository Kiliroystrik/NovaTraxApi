# syntax=docker/dockerfile:1
# Utilisation de l'image PHP 8.3 FPM Alpine

# Etape 1: J'installe la distribution PHP avec les dépendances requises
FROM php:8.3-fpm

# Installation des dépendances nécessaires pour Symfony, PostgreSQL et JWT
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy custom Xdebug configuration
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini


# Etape 2: Je copie le contenu du projet dans le conteneur

# Je définis le répertoire du conteneur dans lequel je vais copier mes fichiers de mon symfony
WORKDIR /var/www/html

# Je copie " . " mon repertoire local vers " . " ce chemin dans le conteneur /var/www/html
COPY . .

# Ajouter un script d'entrée
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# J'expose le port 9000 car c'est celui qui sera utilisé par PHP-FPM
EXPOSE 9000

# Utiliser le script d'entrée pour démarrer le conteneur avec l'installation de composer
ENTRYPOINT ["sh", "/usr/local/bin/entrypoint.sh"]