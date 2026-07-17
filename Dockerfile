FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL y activar las extensiones PDO de PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar el módulo rewrite de Apache por si acaso
RUN a2enmod rewrite

# Copiar todo el contenido del proyecto a la carpeta raíz del servidor Apache
COPY . /var/www/html/

# Exponer el puerto 80 estándar
EXPOSE 80
