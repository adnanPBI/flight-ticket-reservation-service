# Phase 14 — cPanel VPS deployment plan

This phase prepares deployment assets for a Namecheap/cPanel VPS-style setup.

## Assumption

This is for **cPanel VPS with SSH/root or WHM-level control**, not basic shared hosting.

You need persistent background processes for:

```txt
php artisan horizon
php artisan reverb:start
php artisan schedule:run via cron
```

A normal shared cPanel account is usually not enough for this architecture.

## Recommended directory layout

```txt
/home/CPANEL_USER/ota              Laravel app root
/home/CPANEL_USER/ota/public       temporary subdomain document root
/home/CPANEL_USER/ota/storage      writable logs/cache/uploads
/home/CPANEL_USER/ota/bootstrap/cache writable framework cache
```

Do not expose the full Laravel project under `public_html`.

## Temporary domain/subdomain

Create a temporary cPanel subdomain, then set document root to:

```txt
/home/CPANEL_USER/ota/public
```

## Required server services

```txt
PHP 8.3 or 8.4
Composer
Node.js + npm
MySQL 8+
Redis server + phpredis extension
Supervisor or systemd
SSL certificate
Cron
```

## Deployment script

Use:

```bash
deploy/cpanel/deploy-production.sh
```

Before running, replace `CPANEL_USER` placeholders or pass `APP_DIR`:

```bash
APP_DIR=/home/myuser/ota bash deploy/cpanel/deploy-production.sh
```

## Permissions script

Use:

```bash
deploy/cpanel/setup-permissions.sh
```

If you do not have root access, apply equivalent permissions through SSH/cPanel.

## Cron

Add the content from:

```txt
deploy/cpanel/cron.txt
```

Expected cron:

```cron
* * * * * cd /home/CPANEL_USER/ota && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

## Supervisor examples

```txt
deploy/supervisor/horizon.conf.example
deploy/supervisor/reverb.conf.example
deploy/supervisor/queue-worker.conf.example
```

Use Horizon as the primary queue supervisor if possible. The extra queue worker config is provided for fallback/segmented queues.

## systemd alternatives

```txt
deploy/systemd/ota-horizon.service.example
deploy/systemd/ota-reverb.service.example
```

Use systemd only if you have root access and prefer it over Supervisor.

## Production `.env` minimum

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-temp-domain.example.com
FORCE_HTTPS=true
SECURITY_HSTS_ENABLED=true

DB_CONNECTION=mysql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
BROADCAST_CONNECTION=reverb

STRIPE_MOCK_MODE=true
FLIGHT_MOCK_MODE=true
FLIGHT_REAL_ORDER_FINALIZATION=false
```

Keep Stripe and Duffel in mock/sandbox until your payment and provider production approvals are complete.

## After deployment

Run:

```bash
php artisan ota:security-audit-check
php artisan about
php artisan route:list
```

Then check:

```txt
/admin
/flights/search
/manage-booking
/health/secure
```

## Restart commands

Supervisor example:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart ota-horizon
sudo supervisorctl restart ota-reverb
```

systemd example:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now ota-horizon ota-reverb
sudo systemctl restart ota-horizon ota-reverb
```

## cPanel-specific warning

Reverb uses WebSockets. If cPanel/Apache proxying blocks WebSockets, place Reverb behind a supported reverse proxy or use a cloud/VPS environment with direct Nginx configuration. The app code is Reverb-ready, but the server must allow WebSocket upgrade traffic.
