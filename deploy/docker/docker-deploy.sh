#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/ota-flight-booking}"
BRANCH="${BRANCH:-main}"

echo "Deploying optional Docker build in $APP_DIR"
cd "$APP_DIR"

git fetch --all --prune || true
git checkout "$BRANCH" || true
git pull --ff-only || true

cp -n .env.docker.example .env || true

docker compose build --pull
docker compose up -d mysql redis mailpit
docker compose run --rm app composer install --no-dev --prefer-dist --optimize-autoloader
docker compose run --rm app npm ci || docker compose run --rm app npm install
docker compose run --rm app npm run build
docker compose run --rm app php artisan migrate --force
docker compose up -d app nginx horizon reverb scheduler

docker compose exec app php artisan optimize

echo "Done. Check http://localhost:8080 and Mailpit at http://localhost:8025"
