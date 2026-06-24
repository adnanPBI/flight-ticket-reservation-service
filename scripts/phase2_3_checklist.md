# Phase 2–3 Checklist

## Backend

- [ ] `php artisan migrate --seed` works
- [ ] `config/flight.php` loads
- [ ] `FLIGHT_MOCK_MODE=true` returns mock offers
- [ ] `/flights/search` loads
- [ ] Posting search creates `flight_searches`
- [ ] Posting search creates `flight_offers`
- [ ] `flight_provider_logs` records mock search
- [ ] Fare selection creates draft `bookings`
- [ ] Booking status moves to `offer_selected`

## Frontend

- [ ] `npm install` works
- [ ] `npm run build` works
- [ ] Home page loads
- [ ] Flight search page loads
- [ ] Search results page renders offers
- [ ] Fare details page renders price breakdown
- [ ] Select fare redirects to passenger details page
- [ ] Checkout and confirmation skeleton pages render

## Next

- [ ] Add real Duffel sandbox credentials
- [ ] Disable mock mode
- [ ] Verify provider response normalization
- [ ] Harden provider error mapping
- [ ] Start Phase 4 deeper Duffel search integration
