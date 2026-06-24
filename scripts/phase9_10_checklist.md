# Phase 9–10 Checklist

## Pricing

- [ ] `php artisan migrate` adds pricing snapshot fields
- [ ] `php artisan db:seed --class=PromoCodeSeeder` creates `WELCOME10`
- [ ] Booking creation stores pricing snapshot
- [ ] Checkout displays markup/discount/total rows
- [ ] Applying promo updates booking total
- [ ] Applying promo cancels old unpaid payment records
- [ ] Stripe mock PaymentIntent uses the repriced booking total

## Chat

- [ ] `npm install` installs `laravel-echo` and `pusher-js`
- [ ] `php artisan reverb:start` runs locally
- [ ] Chat widget opens on public pages
- [ ] Guest conversation is created
- [ ] Guest can send message
- [ ] Message appears in Filament support inbox
- [ ] Admin can reply from conversation view page
- [ ] User receives reply without page refresh when Reverb is running

## Safety

- [ ] Promo cannot be applied after payment succeeds
- [ ] Expired offers cannot be repriced
- [ ] Customer cannot read/send messages for another visitor token
- [ ] Chat endpoints are rate-limited
