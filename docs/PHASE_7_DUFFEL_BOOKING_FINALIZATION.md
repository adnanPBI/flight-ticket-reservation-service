# Phase 7 — Duffel booking finalization

This phase connects the Phase 6 payment success event to provider order finalization.

## What this phase adds

- `ConfirmBookingJob` now runs provider booking finalization.
- `FinalizeProviderBookingAction` validates succeeded payment before provider order creation.
- `DuffelService::createOrder()` now supports mock orders and a real Duffel order payload structure.
- Provider order fields are stored on `bookings`.
- Booking transitions now move through:
  - `payment_succeeded`
  - `booking_confirming`
  - `booking_confirmed` / `ticketing_pending` / `ticketed` / `booking_failed`
- `RetryTicketingJob` can retrieve provider order status later.
- `SendBookingConfirmationJob` sends a basic email after provider order success.
- `RefundFailedBookingJob` is still a safe manual-review placeholder.

## Safety gates

Default `.env.example` values:

```env
FLIGHT_MOCK_MODE=true
STRIPE_MOCK_MODE=true
STRIPE_DISPATCH_CONFIRM_JOB=true
FLIGHT_REAL_ORDER_FINALIZATION=false
FLIGHT_AUTO_REFUND_FAILED_BOOKINGS=false
```

This means local testing can create a mock payment and mock provider order without charging a real card or creating a real airline order.

## Real Duffel warning

Do not set this to true until all of these are confirmed:

```env
FLIGHT_REAL_ORDER_FINALIZATION=true
```

Required before enabling:

- Duffel account/API access approved.
- Duffel sandbox or production token configured.
- Airline/order payment method verified.
- Stripe flow tested with webhook signing.
- Admin failure queue/review process ready.
- Refund policy approved.

## Local mock test flow

1. Keep mock mode enabled:

```env
FLIGHT_MOCK_MODE=true
STRIPE_MOCK_MODE=true
STRIPE_DISPATCH_CONFIRM_JOB=true
QUEUE_CONNECTION=sync
```

2. Search and select a mock fare.
3. Add passenger details.
4. Create mock payment on checkout.
5. Click/trigger mock payment success.
6. Confirm that booking status becomes `ticketed` and mock PNR/ticket number appear on the confirmation page.

If `QUEUE_CONNECTION=redis`, run:

```bash
php artisan horizon
```

or:

```bash
php artisan queue:work redis
```

## cPanel VPS note

For cPanel VPS, Horizon/Reverb must be persistent processes through Supervisor or systemd. Do not keep them running from an open terminal.

## New migration

```txt
2026_01_01_000013_add_provider_order_fields_to_bookings_table.php
```

Adds:

- `provider_order_status`
- `provider_order_payload`
- `ticketing_last_checked_at`
- `ticketing_retry_count`

## Main classes

```txt
app/Actions/Booking/FinalizeProviderBookingAction.php
app/Actions/Booking/RecordProviderOrderAction.php
app/Actions/Booking/MarkBookingFinalizationFailedAction.php
app/Jobs/ConfirmBookingJob.php
app/Jobs/RetryTicketingJob.php
app/Jobs/SendBookingConfirmationJob.php
app/Jobs/SyncProviderBookingStatusJob.php
app/Mail/BookingConfirmationMail.php
app/Support/ProviderOrderStatusMapper.php
```

## Acceptance checklist

- Payment success dispatches `ConfirmBookingJob`.
- Mock mode creates mock provider order.
- Booking gets provider order ID, PNR, and ticket number in mock mode.
- Booking status moves to `ticketed` in mock mode.
- If real finalization is disabled and mock mode is off, booking moves to `ticketing_pending`/manual review instead of creating a real order.
- Provider API failure moves booking to `booking_failed` where allowed.
- Failed booking logs reason.
- Confirmation page displays provider order status and recent booking events.
