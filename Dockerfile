FROM composer:2.6.5 AS composer

COPY ./ /app
WORKDIR /app

# Run composer to install dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress \
  --no-dev

FROM alpine:3.18.4

# Install packages
RUN apk add --no-cache \
  nginx \
  supervisor \
  php82 \
  php82-curl \
  php82-mbstring \
  php82-simplexml \
  php82-fpm

# Copy nginx config
COPY --chown=nobody /docker/config/nginx.conf /etc/nginx/nginx.conf

# Copy php-fpm config
COPY --chown=nobody /docker/config/fpm-pool.conf /etc/php82/php-fpm.d/www.conf

# Copy supervisord config
COPY --chown=nobody /docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy code
COPY --chown=nobody --from=composer /app/ /app

# Create cache folder
RUN mkdir -p /app/cache

# Make files accessable to nobody user
RUN chown -R nobody.nobody /run /app /var/lib/nginx /var/log/nginx

# Remove setup files
RUN rm -r /app/docker && rm /app/composer.*

USER nobody
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
