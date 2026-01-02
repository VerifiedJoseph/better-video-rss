FROM composer:2.9 AS composer

COPY ./ /app
WORKDIR /app

# Run composer to install dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress \
  --no-dev

FROM alpine:3.23.2

# Install packages
RUN apk add --no-cache \
  nginx \
  curl \
  php83 \
  php83-curl \
  php83-mbstring \
  php83-simplexml \
  php83-fpm

# Copy nginx config
COPY --chown=nobody /docker/config/nginx.conf /etc/nginx/nginx.conf

# Copy php-fpm config
COPY --chown=nobody /docker/config/fpm-pool.conf /etc/php82/php-fpm.d/www.conf

# Copy nginx config
COPY --chown=nobody /docker/config/nginx.conf /etc/nginx/nginx.conf

# Copy entrypoint script
COPY --chown=nobody /docker/entrypoint.sh /entrypoint.sh

# Copy code
COPY --chown=nobody --from=composer /app/ /app

# Create cache folder
RUN mkdir -p /app/cache

# Make files accessible to nobody user
RUN chown -R nobody:nobody /run /app /var/lib/nginx /var/log/nginx

# Remove setup files
RUN rm -r /app/docker && rm /app/composer.*

# php-fpm heath check
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping

USER nobody
ENTRYPOINT ["/bin/sh", "entrypoint.sh"]
