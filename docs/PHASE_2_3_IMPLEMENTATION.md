# Phase 2–3 Implementation Notes

This package continues the Phase 0–1 Laravel OTA foundation.

## What Phase 2 adds

- `FlightProviderInterface`
- `DuffelService`
- `AmadeusService` placeholder/fallback
- `FlightProviderManager`
- `FlightSearchNormalizer`
- Provider request/response logging
- DTO/data objects for normalized offers, segments, search result, order result, and cancellation result
- `StoreFlightSearchAction`
- `config/flight.php`
- Mock-mode provider results when API credentials are missing

## What Phase 3 adds

- Public OTA Inertia React page skeleton
- Mobile-first Tailwind layout
- Flight search page
- Results page
- Fare details page
- Passenger details skeleton
- Checkout skeleton
- Confirmation skeleton
- Manage booking skeleton
- Account login/register placeholders
- Support chat placeholder
- Reusable flight UI components

## Mock mode

Mock mode is enabled by default:

```env
FLIGHT_MOCK_MODE=true
```

This means `/flights/search` will return demo offers without Duffel credentials.

When Duffel sandbox credentials are ready:

```env
FLIGHT_MOCK_MODE=false
DUFFEL_ACCESS_TOKEN=your_sandbox_token
```

## Test flow

1. Run migrations and seeders.
2. Start Laravel and Vite.
3. Open `/flights/search`.
4. Search `DAC` to `CXB` with a future date.
5. Confirm results appear.
6. Open fare details.
7. Select fare.
8. Confirm a draft booking is created and redirected to passenger details skeleton.

## Important limitation

Real Duffel order creation is intentionally not completed in Phase 2–3. That belongs to:

- Phase 6: Stripe PaymentIntent
- Phase 7: Duffel order finalization after payment success

Do not create provider orders before payment lifecycle and failure handling are ready.

## Files added/changed

### Added backend

- `app/Data/FlightSegmentData.php`
- `app/Data/NormalizedFlightOfferData.php`
- `app/Data/FlightSearchResultData.php`
- `app/Data/ProviderOrderResultData.php`
- `app/Data/ProviderCancellationResultData.php`
- `app/Actions/Flight/StoreFlightSearchAction.php`
- `app/Services/Flight/DuffelService.php`
- `app/Services/Flight/AmadeusService.php`
- `app/Services/Flight/FlightProviderManager.php`
- `app/Services/Flight/FlightProviderLogger.php`
- `app/Services/Flight/FlightSearchNormalizer.php`
- `config/flight.php`
- `app/Http/Controllers/Flights/*`

### Added frontend

- `resources/js/Components/Layout/PublicLayout.tsx`
- `resources/js/Components/Flights/*`
- `resources/js/Components/Support/ChatWidget.tsx`
- `resources/js/Pages/Home.tsx`
- `resources/js/Pages/Flights/*`
- `resources/js/Pages/Account/*`
- `resources/js/Pages/ManageBooking/Show.tsx`

## Next phase

Phase 4 should focus on deeper Duffel search hardening:

- Real sandbox search request validation
- Fare refresh/revalidation
- Offer expiry handling
- Segment persistence to `booking_segments`
- Stronger provider error mapping
- Admin flight API log viewer
