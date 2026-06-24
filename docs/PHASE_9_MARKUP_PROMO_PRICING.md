# Phase 9 — Markup and Promo Pricing

This phase adds server-side commercial pricing without changing the provider fare source of truth.

## Included

- `PriceCalculationService`
- `PromoCodeService`
- `PriceQuoteData`
- booking pricing snapshot fields
- applied promo fields on bookings
- checkout promo-code application route
- checkout UI promo form
- seeded `WELCOME10` promo code
- old unpaid PaymentIntent records are cancelled when a promo reprices the booking

## Important rule

The frontend never calculates the payable total. It only displays the amount stored on the booking snapshot.

## Pricing model

Stored on `bookings`:

- `provider_base_amount_minor`
- `tax_amount_minor`
- `fee_amount_minor`
- `markup_amount_minor`
- `discount_amount_minor`
- `total_amount_minor`
- `applied_promo_code_id`
- `applied_promo_code`
- `pricing_snapshot`
- `pricing_locked_at`

## Supported markup calculation types

- `fixed` — value is major currency unit, e.g. `10.00` = 1000 minor units
- `percentage` — percentage of provider base + tax + fee
- `per_passenger_fixed` — fixed amount multiplied by passenger count

## Supported promo discount types

- `fixed`
- `percentage`

## Test promo

```txt
WELCOME10
```

## Safety behavior

If a promo is applied after a PaymentIntent was already prepared but not paid, the old local payment record is marked `cancelled`. The user must create a new PaymentIntent from the updated booking amount.

## Production warning

For real payment mode, do not allow a PaymentIntent amount to be modified from the browser. Always create/recreate it from the server-side booking snapshot.
