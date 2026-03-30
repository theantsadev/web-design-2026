FROM php:8.2-apache

# Activer les modules Apache
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Définir le répertoire root d'Apache
WORKDIR /var/www/html

# Copier la configuration Apache (si besoin)
COPY .htaccess .

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
