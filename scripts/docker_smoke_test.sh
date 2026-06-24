#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"
HEALTH_URL="$BASE_URL/health/secure"

echo "Checking app home..."
curl -fsS "$BASE_URL" >/dev/null

echo "Checking secure health endpoint..."
curl -fsS "$HEALTH_URL" | php -r '$json=json_decode(stream_get_contents(STDIN), true); if(!isset($json["status"])) { exit(1); } echo "health=".$json["status"].PHP_EOL;'

echo "Checking flight search page..."
curl -fsS "$BASE_URL/flights/search" >/dev/null

echo "Docker smoke test passed."
