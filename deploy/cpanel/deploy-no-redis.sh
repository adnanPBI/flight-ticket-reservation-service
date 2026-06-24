#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/home/CPANEL_USER/ota}"
PHP_BIN="${PHP_BIN:-/usr/local/bin/php}"
NPM_BIN="${NPM_BIN:-npm}"

cd "$APP_DIR"

echo "[1/9] Installing PHP dependencies (no dev)..."
composer install --no-dev --optimize-autoloader

echo "[2/9] Installing JS dependencies..."
$NPM_BIN ci || $NPM_BIN install

echo "[3/9] Building Vite assets..."
$NPM_BIN run build

echo "[4/9] Putting app in maintenance mode..."
$PHP_BIN artisan down --render="errors::503" || true

echo "[5/9] Running migrations..."
$PHP_BIN artisan migrate --force

echo "[6/9] Linking storage..."
$PHP_BIN artisan storage:link || true

echo "[7/9] Caching Laravel config/routes/views..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache || true
$PHP_BIN artisan optimize

echo "[8/9] Running security readiness check..."
$PHP_BIN artisan ota:security-audit-check || true

echo "[9/9] Bringing app online..."
$PHP_BIN artisan up || true

echo "Deployment completed (no Redis / no Horizon / no Reverb)."
echo "Jobs are processed by the database queue via cron (see deploy/cpanel/cron-no-redis.txt)."
