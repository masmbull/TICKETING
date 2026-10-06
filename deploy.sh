#!/bin/bash
set -e

# Production is a Docker stack: docker-compose.yml builds the `app` (php-fpm) and
# `nginx` images from the repo-root Dockerfile, plus postgres and redis. There is
# no host PHP/Composer/Node, so a deploy is a git update plus an image rebuild.
# Self-locating: this script lives in the repo root, so APP_DIR is wherever the
# checkout is — no hardcoded absolute path to drift out of sync with the VPS.
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "=== DEPLOY START ==="

# Log which box this actually ran on, so a deploy can never be silently pointed
# at a stale host (the ORIGINAL auto-deploy was authored on an EC2 instance:
# ubuntu@ip-172-31-46-232.ap-southeast-2.compute.internal). A private-DNS
# hostname like ip-<a>-<b>-<c>-<d>.<region>.compute.internal = AWS EC2.
echo "HOST: $(hostname)  IP: $(hostname -I 2>/dev/null | awk '{print $1}')  PATH: ${BASH_SOURCE[0]}"

git fetch origin
git reset --hard origin/main

# The storage volume is declared `external: true` in docker-compose.yml, so it
# must exist before `up` or compose aborts with "volume not found". docker run
# / compose alias no-op if it is already present.
docker volume create ticketing_storage >/dev/null

# Build assets into the image (Dockerfile stage `frontend`), recreate containers,
# then migrate once the new app container is up.
docker compose up -d --build
docker compose exec -T app php artisan migrate --force

# 413 on uploads >1MB is the HOST nginx default (client_max_body_size 1m): it
# terminates TLS for ticketing.mito.co.id and proxies to the container, so it
# rejects the request BEFORE it reaches php-fpm. The container nginx limit lives
# in docker/nginx/default.conf and php-fpm limits in public/.user.ini (tracked).
# Raise the limit on the ticketing vhost ONLY so hris/attendance/other projects'
# vhosts are left untouched. Idempotent: inserted only when absent.
NGINX_SITE="$(grep -RlsE -- 'server_name[[:space:]]+[^;]*ticketing' /etc/nginx/sites-enabled /etc/nginx/conf.d 2>/dev/null | head -n1 || true)"
if [ -n "$NGINX_SITE" ]; then
    if ! sudo grep -q 'client_max_body_size' "$NGINX_SITE"; then
        sudo sed -i 's#\(server_name[[:space:]]\+[^;]*ticketing[^;]*;\)#\1\n    client_max_body_size 20M;#' "$NGINX_SITE"
    fi
    sudo nginx -t && sudo systemctl reload nginx
else
    echo "WARN: ticketing vhost not found under /etc/nginx; skipped client_max_body_size."
fi

echo "=== DEPLOY SUCCESS ==="
