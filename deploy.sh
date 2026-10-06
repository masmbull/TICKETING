#!/bin/bash
set -e

# The CI SSH step runs a non-interactive shell whose PATH is not the login PATH,
# so docker is not guaranteed to be found. Probe the usual install locations and
# prepend whichever one actually contains the binary (works for apt /usr/bin and
# snap /snap/bin alike) instead of hardcoding one.
echo "PATH=${PATH}"
if ! command -v docker >/dev/null 2>&1; then
    for d in /snap/bin /usr/local/bin /usr/bin /usr/sbin /sbin; do
        if [ -x "$d/docker" ]; then PATH="$d:$PATH"; break; fi
    done
fi
if ! command -v docker >/dev/null 2>&1; then
    echo "ERROR: docker not found. PATH=${PATH}"
    ls -l /usr/bin/docker /usr/local/bin/docker /snap/bin/docker 2>&1 || true
    find / -maxdepth 6 -name docker -type f -perm -u+x 2>/dev/null | head -n5 || true
    exit 1
fi
echo "Using docker: $(command -v docker)"

# Production is a Docker stack. Code and assets are baked into the images by the
# multi-stage Dockerfile (build context = this directory), so a deploy is a git
# update plus an image rebuild/recreate. There is no host PHP/Composer/npm.
# Self-locating: this script lives in the repo root, so APP_DIR is wherever the
# checkout is — no hardcoded absolute path to drift out of sync with the VPS.
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "=== DEPLOY START ==="

git fetch origin
git reset --hard origin/main

docker compose up -d --build

docker exec ticketing-app php artisan migrate --force

# Upload limits (upload_max_filesize/post_max_size) come from
# docker/php-uploads.ini, mounted into php-fpm conf.d via docker-compose.yml.
docker exec ticketing-app php -r 'echo "uploads: ", ini_get("upload_max_filesize"), " | ", ini_get("post_max_size"), "\n";'

# Host nginx (Ubuntu apt) reverse-proxies ticketing.mito.co.id -> 127.0.0.1:8082
# and sets NO client_max_body_size, so the 1m default rejects any upload >1MB
# with 413 before it reaches the container (whose own nginx already sets 20M).
# Set the limit on the ticketing vhost ONLY so hris/attendance/other projects'
# vhosts are left untouched. Idempotent: inserted only when absent.
NGINX_SITE="$(grep -Rls -- 'server_name ticketing.mito.co.id' /etc/nginx/sites-enabled /etc/nginx/conf.d 2>/dev/null | head -n1)"
if [ -n "$NGINX_SITE" ]; then
    if ! grep -q 'client_max_body_size' "$NGINX_SITE"; then
        sudo sed -i '/server_name ticketing\.mito\.co\.id;/a\    client_max_body_size 20M;' "$NGINX_SITE"
    fi
    sudo nginx -t && sudo systemctl reload nginx
else
    echo "WARN: ticketing vhost not found under /etc/nginx; skipped client_max_body_size."
fi

echo "=== DEPLOY SUCCESS ==="
