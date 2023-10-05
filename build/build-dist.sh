#!/bin/bash
# Create dist folder
mkdir ./dist

# Copy folders
cp -r ./include ./dist/include
cp -r ./vendor ./dist/vendor

# Copy files
cp ./index.php ./dist/index.php
cp ./feed.php ./dist/feed.php
cp ./proxy.php ./dist/proxy.php
cp ./cache-viewer.php ./dist/cache-viewer.php
cp ./tools.html ./dist/tools.html
cp ./config.php-dist ./dist/config.php-dist
cp ./README.md ./dist/README.md
cp ./LICENSE ./dist/LICENSE
