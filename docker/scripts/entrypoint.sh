#!/usr/bin/env bash
set -e

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ "${OTA_DOCKER_WAIT_FOR_DB:-true}" = "true" ]; then
  echo "Waiting briefly for database..."
  php -r 'for ($i=0;$i<30;$i++){try{$pdo=new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0);}catch(Throwable $e){sleep(1);}} exit(1);'
fi

if [ "${OTA_DOCKER_AUTO_KEY:-true}" = "true" ]; then
  php artisan key:generate --force --no-interaction >/dev/null 2>&1 || true
fi

if [ "${OTA_DOCKER_AUTO_MIGRATE:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

exec "$@"
