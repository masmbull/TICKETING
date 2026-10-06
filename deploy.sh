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

# nginx: app-nginx image already sets `client_max_body_size 20M` in
# /etc/nginx/conf.d/default.conf. Do NOT inject here — a naive sed appends a
# duplicate directive and fails `nginx -t`.
# ponytail: the upload ceiling is PHP, not nginx. Fix upload_max_filesize /
# post_max_size in the app-app image (or a mounted php ini) when that repo is
# available.

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== DEPLOY SUCCESS ==="
