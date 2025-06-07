FROM php:8.1-apache
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git && \
    docker-php-ext-install zip pdo pdo_mysql
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html/storage
EXPOSE 80
CMD ["apache2-foreground"]