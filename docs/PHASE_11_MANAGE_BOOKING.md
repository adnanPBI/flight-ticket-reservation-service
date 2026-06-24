# Phase 11 — Manage Booking

## What this phase adds

Phase 11 converts the placeholder manage-booking pages into a usable customer-facing booking area.

Included:

- Guest booking lookup by booking reference + customer email
- Session-based manage booking access grant
- Account booking history page for logged-in users
- Manage booking details page
- Booking timeline, status, PNR, ticket number, payment summary, passenger list, and flight segments
- HTML receipt view
- Customer last-viewed and lookup tracking fields

## Routes

```txt
GET  /manage-booking
POST /manage-booking
GET  /manage-booking/{booking_reference}
GET  /manage-booking/{booking_reference}/receipt
GET  /account/bookings
```

## Access control rule

A booking can be viewed when one of these is true:

1. The logged-in user owns the booking.
2. The current session has passed guest lookup verification using booking reference + customer email.

This is intentionally stricter than public reference-only access.

## Files added

```txt
app/Actions/Booking/AuthorizeManageBookingAccessAction.php
app/Actions/Booking/BuildManageBookingPayloadAction.php
app/Http/Controllers/Account/BookingHistoryController.php
app/Http/Controllers/ManageBooking/LookupController.php
app/Http/Controllers/ManageBooking/ShowController.php
app/Http/Controllers/ManageBooking/ReceiptController.php
resources/js/Pages/ManageBooking/Lookup.tsx
resources/js/Pages/ManageBooking/Show.tsx
resources/js/Pages/Account/Bookings.tsx
resources/views/receipts/booking.blade.php
database/migrations/2026_01_01_000015_add_phase11_manage_booking_fields_to_bookings_table.php
```

## Testing path

1. Complete the mock flow through search → passengers → payment → confirmation.
2. Copy the booking reference and customer email.
3. Visit `/manage-booking`.
4. Enter the booking reference and email.
5. Verify that the booking page opens.
6. Open the receipt page.

## Not included yet

Cancellation, refunds, reissue/change, PDF receipt generation, and email magic-link login are not included in this phase. They should be handled after provider cancellation/change rules are confirmed.
