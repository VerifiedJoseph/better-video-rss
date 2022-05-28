FROM trafex/php-nginx:2.5.0

# Run commands as root
USER root

# Install php8.0-simplexml
RUN apk add --no-cache php8-simplexml

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Create cache folder & set owner
RUN mkdir -p /var/www/cache/ && chown nobody:nobody /var/www/cache/

# Switch to nobody
USER nobody

# Copy code
COPY --chown=nobody ./ /var/www/html/

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Set env for cache dir
ENV BVRSS_CACHE_DIR=/var/www/cache/
