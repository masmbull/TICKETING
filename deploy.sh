#!/bin/bash
set -e

# Production is a Docker stack. Code and assets are baked into the images by the
# multi-stage Dockerfile (build context = this directory), so a deploy is a git
# update plus an image rebuild/recreate. There is no host PHP/Composer/npm.
APP_DIR="/opt/ticketing/app"
cd "$APP_DIR"

echo "=== DEPLOY START ==="

git fetch origin
git reset --hard origin/main

docker compose up -d --build

docker exec ticketing-app php artisan migrate --force

# Upload limits (upload_max_filesize/post_max_size) come from
# docker/php-uploads.ini, mounted into php-fpm conf.d via docker-compose.yml.
docker exec ticketing-app php -r 'echo "uploads: ", ini_get("upload_max_filesize"), " | ", ini_get("post_max_size"), "\n";'

echo "=== DEPLOY SUCCESS ==="
