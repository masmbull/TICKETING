#!/bin/bash
set -e

# Production is a NATIVE stack: Ubuntu + Nginx + PHP-FPM + Node, not Docker (there
# is no docker binary on the host and no Dockerfile in the repo). A deploy is a
# git update plus host dependency/build/migrate steps. Self-locating: this script
# lives in the repo root, so APP_DIR is wherever the checkout is — no hardcoded
# absolute path to drift out of sync with the VPS.
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "=== DEPLOY START ==="

git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 413 on uploads >1MB is the host nginx default (client_max_body_size 1m), which
# rejects the request BEFORE it reaches PHP-FPM. Raise the limit on the ticketing
# vhost ONLY so hris/attendance/other projects' vhosts are left untouched.
# Idempotent: inserted only when absent. PHP-side limits (upload_max_filesize /
# post_max_size) come from public/.user.ini, tracked in the repo.
NGINX_SITE="$(grep -RlsE -- 'server_name[[:space:]]+[^;]*ticketing' /etc/nginx/sites-enabled /etc/nginx/conf.d 2>/dev/null | head -n1)"
if [ -n "$NGINX_SITE" ]; then
    if ! grep -q 'client_max_body_size' "$NGINX_SITE"; then
        sudo sed -i 's/\(server_name[[:space:]]\+[^;]*ticketing[^;]*;\)/\1\n    client_max_body_size 20M;/' "$NGINX_SITE"
    fi
    sudo nginx -t && sudo systemctl reload nginx
else
    echo "WARN: ticketing vhost not found under /etc/nginx; skipped client_max_body_size."
fi

echo "=== DEPLOY SUCCESS ==="
