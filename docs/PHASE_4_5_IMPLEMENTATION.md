# Phase 4–5 Implementation Notes

This package continues the Phase 0–3 Laravel OTA starter and adds the first real booking-critical flow:

- Phase 4: deeper Duffel offer lifecycle and offer revalidation
- Phase 5: passenger/contact details storage and booking draft progression

## What changed

### Phase 4: Duffel search + offer revalidation

Added backend actions:

```txt
app/Actions/Flight/RevalidateFlightOfferAction.php
app/Actions/Booking/CreateBookingFromOfferAction.php
```

The selected offer is now revalidated before a booking draft is created. In mock mode, the local mock offer is accepted after expiry checks. In real Duffel mode, the connector retrieves the selected provider offer and updates the stored offer snapshot before creating the booking.

The booking draft now snapshots:

```txt
booking row
booking_segments
booking_price_breakdowns
booking_events
audit_logs
```

This prevents later checkout/payment from depending only on volatile search results.

### Phase 5: Passenger details

Added request validation and storage action:

```txt
app/Http/Requests/StorePassengerDetailsRequest.php
app/Actions/Booking/StorePassengerDetailsAction.php
```

The passenger step now:

1. Requires contact email and phone
2. Requires the passenger count to match the original search
3. Validates passenger type counts: adult, child, infant_without_seat
4. Stores passenger records against the booking
5. Updates customer email/phone on the booking
6. Transitions booking status from `offer_selected` to `passenger_details_added`
7. Blocks checkout until passenger details are saved

## New/updated routes

```php
GET  /flights/bookings/{booking:booking_reference}/passengers
POST /flights/bookings/{booking:booking_reference}/passengers
GET  /flights/bookings/{booking:booking_reference}/checkout
```

## Expected status flow after Phase 5

```txt
search_created
↓
offer_selected
↓
passenger_details_added
```

Phase 6 will continue from `passenger_details_added` into `payment_pending`.

## Important behavior

### Offer expiry protection

If a selected offer has expired, the passenger form and offer selection block progression. The user must search again.

### Price snapshot

On offer selection, the system stores price breakdown rows:

```txt
Base fare
Taxes
Fees
Platform markup
Discount
Total
```

This makes the checkout screen deterministic even if the provider search result disappears later.

### Passenger count enforcement

The submitted passenger array must match the search counts. Example:

```txt
Search: 2 adults, 1 child, 0 infants
Submit: exactly 2 adult passengers + 1 child passenger
```

If the count mismatches, validation fails.

## Environment additions

```env
FLIGHT_OFFER_REVALIDATION_ENABLED=true
FLIGHT_FAIL_ON_EXPIRED_OFFER=true
```

Mock mode is still enabled by default:

```env
FLIGHT_MOCK_MODE=true
```

## Manual test path

1. Visit `/flights/search`
2. Search DAC → CXB
3. Open a fare
4. Select the fare
5. Confirm you are redirected to passenger details
6. Fill contact and passenger information
7. Submit
8. Confirm redirect to checkout
9. Confirm checkout now displays saved passengers and price snapshot

## Not included yet

The following are intentionally left for later phases:

```txt
Stripe PaymentIntent creation
Stripe webhooks
Duffel order finalization
Real ticketing
Refund workflow
Filament booking/payment resources
Live Reverb support chat
```

Do not add payment until the passenger and selected-offer flow is stable.
