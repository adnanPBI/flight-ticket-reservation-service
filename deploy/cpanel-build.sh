#!/usr/bin/env bash
set -euo pipefail

composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "Deployment build completed."
