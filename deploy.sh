#!/bin/bash
set -e

cd /home/ubuntu/ticketing

echo "=== DEPLOY START ==="

git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

# Fix nginx upload limit (idempotent)
# ponytail: edits container config file; lost on container recreate. Persist via volume mount or baked image config when the app-nginx repo is available.
# Container mode (Docker)
if docker ps --format '{{.Names}}' | grep -q '^ticketing-nginx$'; then
    docker exec ticketing-nginx sh -c '
        CONF=/etc/nginx/conf.d/default.conf
        if [ -f "$CONF" ] && ! grep -q client_max_body_size "$CONF"; then
            sed -i "/server {/a\\    client_max_body_size 10m;" "$CONF"
        fi
        nginx -t && nginx -s reload
    '
# Host mode (bare metal)
elif [ -f "/etc/nginx/sites-available/default" ]; then
    NGINX_CONF="/etc/nginx/sites-available/default"
    if ! grep -q 'client_max_body_size' "$NGINX_CONF"; then
        sudo sed -i '/server {/a\    client_max_body_size 10m;' "$NGINX_CONF"
    fi
    sudo nginx -t && sudo systemctl reload nginx
fi

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== DEPLOY SUCCESS ==="
