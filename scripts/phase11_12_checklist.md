# Phase 11–12 Checklist

## Phase 11 manage booking

- [ ] Run migrations.
- [ ] Complete a mock booking flow.
- [ ] Open `/manage-booking`.
- [ ] Verify booking reference + customer email lookup works.
- [ ] Verify wrong email/reference is rejected.
- [ ] Verify `/manage-booking/{reference}` is blocked before lookup.
- [ ] Verify booking details show status, PNR, payment, passengers, and segments.
- [ ] Verify receipt view opens after access is granted.
- [ ] Verify logged-in account booking list can render.

## Phase 12 scheduled cleanup

- [ ] Confirm `QUEUE_CONNECTION=redis`.
- [ ] Start Horizon.
- [ ] Add cPanel cron for `php artisan schedule:run`.
- [ ] Confirm `php artisan schedule:list` shows cleanup jobs.
- [ ] Run `php artisan schedule:run` manually in staging.
- [ ] Confirm expired search cleanup logs safely.
- [ ] Confirm abandoned payment cleanup does not cancel successful payments.
- [ ] Confirm pending ticketing alert logs bookings requiring admin review.

## cPanel VPS warning

- [ ] Do not rely on an open terminal for queue/scheduler processes.
- [ ] Use Supervisor/systemd for Horizon and Reverb.
- [ ] Use cPanel cron only for `schedule:run`.
