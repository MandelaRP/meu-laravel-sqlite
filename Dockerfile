# Use PHP 8.2 com Apache
FROM php:8.2-apache

# Instalar dependências do sistema necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_sqlite zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar o projeto
COPY . /var/www/html

WORKDIR /var/www/html

# Instalar dependências do Laravel
RUN composer install --no-interaction --optimize-autoloader

# Expor a porta 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

