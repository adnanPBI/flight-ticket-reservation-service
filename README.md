# OTA Flight Booking — Laravel 13 Full MVP Scaffold through Phase 16

This package is the cumulative Laravel OTA flight booking starter from Phase 0 through Phase 16.

## Stack

```txt
Laravel 13
MySQL 8+
Redis
Laravel Horizon
Laravel Reverb
Filament Admin
Inertia.js + React
Tailwind CSS + Vite
Stripe PaymentIntent safe/mock flow
Duffel-first provider abstraction
Amadeus placeholder/fallback structure
cPanel VPS deployment first
Docker optional
```

## Latest added phases

```txt
Phase 15: Optional Docker/cloud deployment path
Phase 16: QA, UAT, screencast, and handover pack
```

## Safe defaults

```env
STRIPE_MOCK_MODE=true
FLIGHT_MOCK_MODE=true
FLIGHT_REAL_ORDER_FINALIZATION=false
FLIGHT_AUTO_REFUND_FAILED_BOOKINGS=false
```

These defaults prevent accidental real card charging or real airline order finalization.

## Local install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Optional Docker install

```bash
cp .env.docker.example .env
docker compose build
docker compose up -d mysql redis mailpit
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --seed
docker compose up -d app nginx horizon reverb scheduler
```

Open:

```txt
http://localhost:8080
http://localhost:8025
```

## QA commands

```bash
php artisan ota:security-audit-check
php artisan ota:handover-check
php artisan ota:uat-report
php artisan test
npm run build
```

## Main docs

```txt
docs/PHASE_13_SECURITY_AUDIT_HARDENING.md
docs/PHASE_14_CPANEL_VPS_DEPLOYMENT.md
docs/PHASE_15_DOCKER_OPTIONAL.md
docs/PHASE_16_QA_HANDOVER.md
qa/UAT_TEST_PLAN.md
qa/HANDOVER_CHECKLIST.md
qa/SCREencast_SCRIPT.md
qa/PRODUCTION_GO_LIVE_CHECKLIST.md
```

## Acceptance path

```txt
Search flight
→ select fare
→ passenger details
→ mock/test payment
→ provider booking finalization job
→ PNR/ticket snapshot
→ admin verification
→ manage booking
→ support chat
```

## Important caveat

This ZIP intentionally excludes `vendor/` and `node_modules/`. Install dependencies on the target environment.
