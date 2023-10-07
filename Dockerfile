FROM composer:2.6.5 AS composer

COPY ./ /app
WORKDIR /app

# Run composer to install dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress \
  --no-dev

FROM php:8.2.11-fpm-alpine3.18

# Install packages
 RUN apk add --no-cache \
  nginx=~1.24.0-r6 \
  supervisor=~4.2.5-r2

# Copy nginx config
COPY --chown=nobody /docker/config/nginx.conf /etc/nginx/nginx.conf

# Copy php-fpm config
COPY --chown=nobody /docker/config/fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Remove zz-docker.conf
RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy supervisord config
COPY --chown=nobody /docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy code
COPY --chown=nobody --from=composer /app/ /app

# Create cache folder
RUN mkdir -p /app/cache

# Make files accessable to nobody user
RUN chown -R nobody.nobody /run /app /var/lib/nginx /var/log/nginx

# Remove setup files
RUN rm -r /app/docker
RUN rm /app/composer.*

USER nobody
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
