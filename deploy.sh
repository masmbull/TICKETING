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

# Host nginx (Ubuntu package) reverse-proxies to 127.0.0.1:8082 and enforces its
# own client_max_body_size (default 1m) — a >1MB upload gets 413 before it ever
# reaches the container/PHP. The container image sets 20M itself, but the host
# does not, so re-apply the drop-in on every deploy (idempotent; /etc/nginx/conf.d
# files are included inside http{}) so a rebuild can never silently regress it.
if command -v nginx >/dev/null 2>&1 && [ -f /etc/nginx/nginx.conf ]; then
    echo 'client_max_body_size 20M;' | sudo tee /etc/nginx/conf.d/zz-ticketing-uploads.conf >/dev/null
    sudo nginx -t && sudo systemctl reload nginx
fi

echo "=== DEPLOY SUCCESS ==="
