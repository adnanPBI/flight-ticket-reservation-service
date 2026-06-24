# Phase 6 Checklist

## Backend

- [ ] `config/stripe.php` exists
- [ ] `.env.example` includes Stripe mock/test keys
- [ ] PaymentIntent endpoint returns JSON
- [ ] Mock success endpoint works only in mock mode
- [ ] Webhook endpoint excludes CSRF only for `/webhooks/stripe`
- [ ] Webhook verifies Stripe signature when real mode is enabled
- [ ] Duplicate webhook event is ignored
- [ ] Payment status transitions through `PaymentStateMachine`
- [ ] Booking status moves to `payment_succeeded` after successful payment
- [ ] `ConfirmBookingJob` remains placeholder until Phase 7

## Frontend

- [ ] Checkout page shows saved booking snapshot
- [ ] Prepare secure payment button calls backend
- [ ] Mock mode shows safe simulation panel
- [ ] Real mode renders Stripe Payment Element
- [ ] Confirmation page shows payment status

## Safety

- [ ] `STRIPE_MOCK_MODE=true` by default
- [ ] `STRIPE_DISPATCH_CONFIRM_JOB=false` by default
- [ ] No provider booking/ticketing happens in Phase 6
