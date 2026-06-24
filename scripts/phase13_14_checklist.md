# Phase 13–14 checklist

## Phase 13 security/audit

- [ ] `php artisan migrate` creates `security_events`
- [ ] `/health/secure` returns JSON status
- [ ] Security headers appear in browser/network response
- [ ] `php artisan ota:security-audit-check` runs
- [ ] Flight search route is rate limited by `flight-search`
- [ ] Payment route is rate limited by `payment`
- [ ] Stripe webhook route is rate limited by `payment-webhook`
- [ ] Manage booking lookup is rate limited by `manage-booking`
- [ ] Audit logs mask sensitive values
- [ ] Provider logs mask sensitive values
- [ ] Filament shows Security Events resource
- [ ] Dev-only state-machine route is unavailable when `APP_DEBUG=false`

## Phase 14 cPanel VPS

- [ ] Temporary subdomain document root points to `/home/CPANEL_USER/ota/public`
- [ ] PHP 8.3/8.4 is selected
- [ ] Composer works over SSH
- [ ] Node/npm works over SSH
- [ ] MySQL database/user created in cPanel
- [ ] Redis installed and reachable
- [ ] `phpredis` extension enabled or Redis client adjusted
- [ ] `.env` copied and production values set
- [ ] `deploy/cpanel/deploy-production.sh` runs
- [ ] `storage` and `bootstrap/cache` are writable
- [ ] Cron scheduler is added
- [ ] Horizon is managed by Supervisor/systemd
- [ ] Reverb is managed by Supervisor/systemd
- [ ] SSL is active
- [ ] `/admin`, `/flights/search`, `/manage-booking`, `/health/secure` load over HTTPS
