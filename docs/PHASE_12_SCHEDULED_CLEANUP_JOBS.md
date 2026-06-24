# Phase 12 — Scheduled Cleanup Jobs

## What this phase adds

Phase 12 makes the previously placeholder cleanup jobs operational and adds scheduled maintenance for abandoned payments and pending ticketing alerts.

Included jobs:

```txt
ExpireOldSearchSessionsJob
ClearOldFlightOfferPayloadsJob
DeleteAbandonedPaymentSessionsJob
SyncProviderBookingStatusJob
SendPendingTicketingAlertJob
```

## Schedule

Defined in `routes/console.php`:

```php
Schedule::job(new ExpireOldSearchSessionsJob)->everyFifteenMinutes();
Schedule::job(new ClearOldFlightOfferPayloadsJob)->dailyAt('02:00');
Schedule::job(new DeleteAbandonedPaymentSessionsJob)->everyThirtyMinutes();
Schedule::job(new SyncProviderBookingStatusJob)->everyFifteenMinutes();
Schedule::job(new SendPendingTicketingAlertJob)->hourly();
```

## cPanel VPS cron

Use one cron entry only:

```bash
* * * * * cd /home/YOUR_CPANEL_USER/YOUR_APP && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Do not run each cleanup job manually through separate cron entries unless you have a strong reason.

## Cleanup policy

Default `.env` values:

```env
CLEANUP_EXPIRED_SEARCH_LIMIT=500
CLEANUP_RAW_OFFER_PAYLOAD_RETENTION_DAYS=14
CLEANUP_PROVIDER_LOG_PAYLOAD_RETENTION_DAYS=30
CLEANUP_ABANDONED_PAYMENT_MINUTES=120
CLEANUP_PENDING_TICKETING_ALERT_MINUTES=60
```

## Safety behavior

- Expired search cleanup clears raw offer payloads for unbooked offers.
- Confirmed booking data is not deleted.
- Provider logs linked to bookings are retained.
- Old provider logs without bookings have raw request/response payloads cleared.
- Abandoned payments are marked cancelled through the payment state machine.
- Pending ticketing alerts are logged for admin/Horizon monitoring.

## Files added/updated

```txt
config/cleanup.php
app/Jobs/ExpireOldSearchSessionsJob.php
app/Jobs/ClearOldFlightOfferPayloadsJob.php
app/Jobs/DeleteAbandonedPaymentSessionsJob.php
app/Jobs/SendPendingTicketingAlertJob.php
routes/console.php
.env.example
```

## Production requirement

On cPanel VPS, the Laravel scheduler must run through cron, and Horizon must run through Supervisor/systemd. If the scheduler is not running, expired search sessions and abandoned payments will not be cleaned up.
