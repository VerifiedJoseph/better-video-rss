FROM php:7.4-apache

# Install zip and unzip (used by Composer)
RUN apt-get update && apt-get install zip unzip -y

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy code
COPY --chown=www-data:www-data ./ /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install dependencies via composer
RUN composer install --prefer-dist --no-dev

# Start apache2
CMD apache2-foreground
