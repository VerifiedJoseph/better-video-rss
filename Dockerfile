ARG COMPOSER_VERSION=2.4
ARG PHP_NGINX_VERSION=2.6.0

FROM composer:${COMPOSER_VERSION} AS composer

# Copy code
COPY ./ /app

WORKDIR /app

# Run composer install to install the dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress \
  --no-dev

FROM trafex/php-nginx:${PHP_NGINX_VERSION}

# Run commands as root
USER root

# Install php81-simplexml
RUN apk add --no-cache php81-simplexml

# Configure nginx
COPY --chown=nobody config/nginx.conf /etc/nginx/nginx.conf

# Create cache folder & set owner
RUN mkdir -p /var/www/cache/ && chown nobody:nobody /var/www/cache/

# Switch to nobody
USER nobody

# Copy code
COPY --chown=nobody --from=composer /app /var/www/html/

# Set env for cache dir
ENV BVRSS_CACHE_DIR=/var/www/cache/

# Configure healthcheck (overrides base image healthcheck)
HEALTHCHECK --interval=60s --timeout=10s CMD curl --silent --fail http://127.0.0.1/fpm-ping
