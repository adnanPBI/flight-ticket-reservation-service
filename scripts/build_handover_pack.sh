#!/usr/bin/env bash
set -euo pipefail

STAMP="$(date +%Y%m%d_%H%M%S)"
OUT="handover-pack-$STAMP"
mkdir -p "$OUT"

cp -r docs qa scripts "$OUT/"
cp .env.example .env.docker.example composer.json package.json README.md "$OUT/" 2>/dev/null || true

cat > "$OUT/HANDOVER_INDEX.md" <<'TXT'
# OTA Flight Booking Handover Pack

Review in this order:
1. docs/PHASE_16_QA_HANDOVER.md
2. qa/UAT_TEST_PLAN.md
3. qa/END_TO_END_TEST_MATRIX.md
4. qa/SCREencast_SCRIPT.md
5. docs/PHASE_14_CPANEL_VPS_DEPLOYMENT.md
6. docs/PHASE_15_DOCKER_OPTIONAL.md
TXT

zip -qr "$OUT.zip" "$OUT"
echo "Created $OUT.zip"
