# Phase 16 — QA, UAT, and Handover

Phase 16 does not add new booking features. It packages the project for verification, client acceptance, and future maintenance.

## Included assets

```txt
qa/UAT_TEST_PLAN.md
qa/END_TO_END_TEST_MATRIX.md
qa/DEFECT_LOG_TEMPLATE.md
qa/HANDOVER_CHECKLIST.md
qa/SCREencast_SCRIPT.md
qa/PRODUCTION_GO_LIVE_CHECKLIST.md
postman/ota-flight-booking-phase16.postman_collection.json
scripts/run_qa_suite.sh
scripts/build_handover_pack.sh
app/Console/Commands/HandoverCheckCommand.php
app/Console/Commands/GenerateUatReportCommand.php
```

## Recommended QA order

```txt
1. Install dependencies
2. Run migrations and seeders
3. Run PHP syntax check
4. Run PHPUnit tests
5. Run npm build
6. Run browser UAT checklist
7. Run mock end-to-end booking
8. Verify Filament admin view
9. Verify chat flow
10. Prepare handover pack and screencast
```

## Commands

```bash
composer install
npm install
php artisan migrate --seed
php artisan ota:security-audit-check
php artisan ota:handover-check
php artisan ota:uat-report
php artisan test
npm run build
```

Use this wrapper if dependencies are installed:

```bash
bash scripts/run_qa_suite.sh
```

## Critical acceptance path

The release is accepted only when this path is verified:

```txt
Search flight
→ select fare
→ passenger details
→ create PaymentIntent/mock payment
→ payment succeeded
→ provider booking finalization job
→ PNR/ticket snapshot stored
→ confirmation visible
→ booking visible in Filament
→ support chat works
→ manage booking lookup works
```

## What remains out of scope

Do not mark these as missing bugs unless they were later added to scope:

```txt
seat map selection
paid extra baggage
real refund automation
flight change/reissue
multi-provider fare comparison
agent commission portal
native mobile apps
hotel/car booking
```

## Handover pack generation

```bash
bash scripts/build_handover_pack.sh
```

This creates a timestamped ZIP containing docs, QA material, and operational checklists.

## Sign-off rule

Do not hand over as production-ready if any of these are true:

```txt
APP_DEBUG=true in production
STRIPE_MOCK_MODE=true in production without written approval
FLIGHT_MOCK_MODE=true in production without written approval
Horizon/Reverb not supervised
Stripe webhook secret missing
Flight provider credentials missing for real mode
No database backup plan
No failed-booking operational process
```
