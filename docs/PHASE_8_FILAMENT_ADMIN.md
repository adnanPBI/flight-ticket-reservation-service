# Phase 8 — Filament Admin Comprehensive Layer

This phase adds the operational admin layer on top of the OTA core built in Phases 0–7.

## Included admin sections

- Operations command center
- Bookings
- Failed bookings
- Payments
- Customers
- Passenger relation management through bookings
- Booking segments
- Booking price breakdowns
- Booking events
- Provider API logs
- Support conversations
- Markup rules
- Promo codes
- Airports
- Airlines
- Admin users
- Support agents
- Audit logs

## Important safety rule

Admin actions do not bypass the booking state machine. Retry, refund-pending, and manual ticket recording actions are routed through action classes and/or state transition services.

## Admin URL

```txt
/admin
```

## Seeded admin user

Use the seeded admin from the earlier phase if you have run:

```bash
php artisan migrate --seed
```

Check `database/seeders/AdminUserSeeder.php` for the default credentials used in this starter. Change it immediately after first login.

## Key operational views

### Command center

Shows booking/payment/support summary widgets and operational reminders.

### Bookings

Primary admin resource for:

- searching by booking reference
- checking status
- viewing provider order ID
- seeing PNR/ticket data
- reviewing passengers, segments, payments, price breakdowns, and booking events
- retrying booking finalization
- recording a manual ticket number
- marking refund pending

### Failed bookings

Filtered queue for:

- booking_failed
- ticketing_pending
- refund_pending

Use this screen for operational triage after payment/provider mismatches.

### Provider logs

Use this before retrying or escalating. It stores request/response/error metadata from Duffel/Amadeus connectors.

## cPanel VPS note

Filament is PHP/server-rendered and works well on a cPanel VPS, but Horizon and Reverb still need persistent process supervision through Supervisor or systemd.

## Phase 8 acceptance checklist

- `/admin` loads
- active admin user can login
- inactive admin user is blocked
- admin can view bookings
- admin can inspect booking relations
- admin can view payments
- admin can inspect provider logs
- failed bookings are separated
- retry action dispatches finalization jobs
- manual ticket action records PNR/ticket and updates booking state
- audit logs are visible
- support conversations are visible
- markup/promo records are manageable
