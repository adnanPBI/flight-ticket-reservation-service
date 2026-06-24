# Phase 0–1 Implementation Notes

## Phase 0 — Foundation

Implemented:

- Laravel 13 composer skeleton
- Inertia React shell
- Tailwind + Vite config
- Redis/Horizon/Reverb env assumptions
- Public layout and starter pages
- Admin/user separation at database level

Not fully implemented yet:

- Filament resources
- Auth screens
- Live Reverb chat
- Stripe
- Duffel/Amadeus calls

## Phase 1 — Database + State Machine

Implemented:

- MySQL migrations for core OTA entities
- Enums for booking/payment/provider/user role
- BookingStateMachine
- PaymentStateMachine
- Booking events
- Payment events
- Audit logs
- Seeders for airports, airlines, admin, support agent, markup rule

## State transition rule

Never update `bookings.status` or `payments.status` directly in business logic.

Use:

```php
app(BookingStateMachine::class)->transition($booking, BookingStatus::OfferSelected);
app(PaymentStateMachine::class)->transition($payment, PaymentStatus::Succeeded);
```

This ensures event/audit records are always created.

## Next phase

Build Phase 2:

- Real `DuffelConnector`
- `AmadeusConnector` placeholder
- Normalized offer result format
- Provider request/response logging
