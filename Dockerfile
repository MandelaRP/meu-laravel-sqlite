# Use PHP 8.2 com Apache
FROM php:8.2-apache

# Instalar dependências do Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_sqlite

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar Apache para Laravel
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar o projeto
COPY . /var/www/html

WORKDIR /var/www/html

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Instalar dependências Laravel (sem dev para produção)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Criar database.sqlite se não existir
RUN touch database/database.sqlite && chmod 664 database/database.sqlite

# Expor a porta do Apache
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

