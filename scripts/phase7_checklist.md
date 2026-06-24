# Phase 7 checklist

- [ ] Run `php artisan migrate`.
- [ ] Keep `FLIGHT_MOCK_MODE=true` for first test.
- [ ] Keep `STRIPE_MOCK_MODE=true` for first test.
- [ ] Set `STRIPE_DISPATCH_CONFIRM_JOB=true`.
- [ ] For instant local test, set `QUEUE_CONNECTION=sync`; otherwise run Horizon/queue worker.
- [ ] Search/select a fare.
- [ ] Add passenger details.
- [ ] Create payment intent.
- [ ] Trigger mock payment success.
- [ ] Confirm booking moves to `ticketed` in mock mode.
- [ ] Confirm provider order ID/PNR/ticket number show on confirmation page.
- [ ] Confirm booking events are recorded.
- [ ] Test real Duffel only after setting `FLIGHT_REAL_ORDER_FINALIZATION=true` deliberately.
