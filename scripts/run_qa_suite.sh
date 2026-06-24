#!/usr/bin/env bash
set -euo pipefail

php artisan ota:security-audit-check || true
php artisan ota:handover-check || true
php artisan test
npm run build
npm run typecheck || true

echo "QA suite completed. Review warnings above before production handover."
