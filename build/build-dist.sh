#!/bin/bash
# Create dist folder
mkdir ./dist

# Copy folders
cp -r ./docs ./dist/docs
cp -r ./include ./dist/include
cp -r ./vendor ./dist/vendor
cp -r ./static ./dist/static

# Copy files
cp ./index.php ./dist/index.php
cp ./feed.php ./dist/feed.php
cp ./cache-viewer.php ./dist/cache-viewer.php
cp ./tools.html ./dist/tools.html
cp ./config.example.php ./dist/config.example.php
cp ./README.md ./dist/README.md
cp ./CHANGELOG.md ./dist/CHANGELOG.md
cp ./LICENSE ./dist/LICENSE
