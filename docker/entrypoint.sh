#!/bin/sh

php-fpm82 -D
nginx -g 'daemon off;'
