# Phase 6 — Stripe PaymentIntent Integration

## Purpose

Phase 6 adds checkout/payment preparation without finalizing the flight booking with Duffel. This prevents accidental ticketing during payment testing.

## Safety defaults

```env
STRIPE_MOCK_MODE=true
STRIPE_DISPATCH_CONFIRM_JOB=false
```

When `STRIPE_MOCK_MODE=true`:

- no Stripe API call is made
- no card form is shown
- no customer can be charged
- the mock success button updates internal payment/booking statuses only

## New backend files

```text
config/stripe.php
app/Services/Payment/StripePaymentService.php
app/Actions/Payment/CreateStripePaymentIntentAction.php
app/Actions/Payment/MarkPaymentSucceededAction.php
app/Http/Controllers/Payments/StripePaymentIntentController.php
app/Http/Controllers/Payments/MockStripePaymentSuccessController.php
app/Http/Controllers/Payments/StripeWebhookController.php
```

## New frontend files

```text
resources/js/Components/Payments/StripePaymentPanel.tsx
resources/js/Pages/Flights/Checkout.tsx
resources/js/Pages/Flights/Confirmation.tsx
```

## New routes

```text
POST /flights/bookings/{booking}/payment-intent
POST /flights/bookings/{booking}/payments/{payment}/mock-succeed
POST /webhooks/stripe
```

## Phase 6 flow

```text
Passenger details saved
↓
Checkout page opens
↓
User clicks Prepare secure payment
↓
Backend creates/reuses Payment record
↓
Backend creates Stripe PaymentIntent or mock intent
↓
Booking moves to payment_pending
↓
Payment moves to requires_payment_method
↓
Mock success or Stripe webhook marks payment succeeded
↓
Booking moves to payment_succeeded
```

## What Phase 6 deliberately does not do

```text
Does not create Duffel order
Does not issue ticket
Does not send real confirmation email
Does not process refunds
Does not retry provider booking
```

Those belong to Phase 7.

## Real Stripe test-mode checklist

Before setting `STRIPE_MOCK_MODE=false`:

```text
Stripe test publishable key exists
Stripe test secret key exists
Stripe webhook endpoint is configured
STRIPE_WEBHOOK_SECRET is added to .env
HTTPS works on cPanel temporary domain
Stripe webhook route is reachable
Webhook signature verification passes
STRIPE_DISPATCH_CONFIRM_JOB remains false until Phase 7
```

## Webhook behavior

Supported event types:

```text
payment_intent.succeeded
payment_intent.payment_failed
payment_intent.canceled
payment_intent.processing
payment_intent.requires_action
```

Duplicate events are ignored through `payment_events.provider_event_id`.

## Important warning

Never enable real Stripe mode in production without Stripe webhook verification. Browser redirects are not enough to prove payment success. The server-side webhook is the source of truth.
