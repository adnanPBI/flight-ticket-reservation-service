#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/home/CPANEL_USER/ota}"
CPANEL_USER="${CPANEL_USER:-CPANEL_USER}"

cd "$APP_DIR"

find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

# Use this only if you have root/WHM access. Otherwise set permissions from cPanel File Manager/SSH.
if command -v chown >/dev/null 2>&1 && [ "$(id -u)" = "0" ]; then
  chown -R "$CPANEL_USER:$CPANEL_USER" storage bootstrap/cache
fi

echo "Permissions prepared for storage and bootstrap/cache."
