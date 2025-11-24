# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias de sistema y Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar extensiones necesarias de PHP (mysqli y pdo para DB)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto al contenedor (IMPORTANTE: Copia todo, incluyendo 000-default.conf)
COPY . /var/www/html/

# Configurar el directorio de trabajo y ejecutar Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar archivos del proyecto al contenedor
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]