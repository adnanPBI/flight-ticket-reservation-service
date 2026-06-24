# Phase 4–5 Checklist

## Phase 4

- [ ] Search creates `flight_searches`
- [ ] Search stores `flight_offers`
- [ ] Fare details page opens
- [ ] Selecting a fare revalidates expiry
- [ ] Real provider mode calls `getOffer()`
- [ ] Booking draft is created
- [ ] Booking status moves to `offer_selected`
- [ ] Booking segments are snapshotted
- [ ] Booking price breakdown is snapshotted
- [ ] Booking event and audit log are created

## Phase 5

- [ ] Passenger form renders according to search counts
- [ ] Contact email and phone are required
- [ ] Passenger type counts are enforced
- [ ] Passenger details save to `booking_passengers`
- [ ] Booking status moves to `passenger_details_added`
- [ ] Checkout is blocked before passenger details
- [ ] Checkout displays saved passengers
- [ ] Checkout displays price snapshot

## Failure checks

- [ ] Expired offer cannot be selected
- [ ] Expired selected booking cannot accept passenger details
- [ ] Wrong passenger count fails validation
- [ ] Invalid DOB fails validation
- [ ] Invalid passport expiry fails validation
