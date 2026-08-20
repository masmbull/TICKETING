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

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo systemctl reload nginx

echo "=== DEPLOY SUCCESS ==="
