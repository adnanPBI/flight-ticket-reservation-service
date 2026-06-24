# cPanel VPS deployment — UI + backend only (no Redis, no Horizon, no Reverb)

This is the lightweight cPanel path. It runs the Laravel app, Filament admin,
Inertia/React UI, and Stripe (mock/sandbox) on a standard cPanel VPS using
**MySQL** for cache, sessions, and the job queue. No Redis, no WebSocket server,
no long-running daemons are required.

> If you later need real-time chat/broadcasting, switch back to the full stack
> in `docs/PHASE_14_CPANEL_VPS_DEPLOYMENT.md` (Redis + Reverb + Horizon).

## What is disabled in this mode

| Feature | Status | Replacement |
|---------|--------|-------------|
| Laravel Horizon | OFF | Database queue via cron |
| Laravel Reverb (WebSockets) | OFF | Broadcasting set to `null` |
| Redis | OFF | MySQL for cache / session / queue |

Support chat UI will still render, but live push will not fire. Async work
(bookings, payment confirmation jobs, cleanups) is handled by the database
queue polled once a minute via cron.

## 1. cPanel prerequisites

In **cPanel → Software → Select PHP Version**, enable:

- PHP **8.3** or **8.4**
- Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`,
  `json`, `bcmath`, `curl`, `gd` or `imagick`, `fileinfo`, `zip`
- Disable: `redis`, `igbinary` (not needed)

Install via **cPanel → Terminal** (or SSH):
- `composer` (cPanel often provides it; otherwise install to `~/bin/composer`)
- `node` + `npm` (only needed to build assets; can build locally and upload the
  built `public/build` folder instead)

## 2. Create a database

**cPanel → MySQL Databases**:

- Database: `cpaneluser_ota`
- User: `cpaneluser_ota`
- Grant **ALL PRIVILEGES**

Note the host (usually `localhost`), DB name, user, and password.

## 3. Place the project outside public_html

Do **not** dump the whole project in `public_html`. Use a temporary subdomain:

**cPanel → Domains → Subdomains** → create e.g. `ota.yourdomain.com` with
document root:

```
/home/CPANEL_USER/ota/public
```

Upload (or `git clone`) the full project to `/home/CPANEL_USER/ota`.

## 4. Production `.env`

Copy `.env.example` to `.env` in the project root and set:

```env
APP_NAME="OTA Flight Booking"
APP_ENV=production
APP_KEY=                          # generated in step 5
APP_DEBUG=false
APP_URL=https://ota.yourdomain.com
FORCE_HTTPS=true
SECURITY_HSTS_ENABLED=true
SECURITY_HEADERS_ENABLED=true

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

# --- No-Redis drivers: everything on MySQL ---
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_ota
DB_USERNAME=cpaneluser_ota
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=null

# (Reverb/Horizon/Redis vars can stay as-is; they are ignored.)

# --- Safe defaults until go-live ---
STRIPE_MOCK_MODE=true
FLIGHT_MOCK_MODE=true
FLIGHT_REAL_ORDER_FINALIZATION=false
FLIGHT_AUTO_REFUND_FAILED_BOOKINGS=false
```

## 5. Build & deploy over SSH/Terminal

```bash
cd /home/CPANEL_USER/ota

# Generate app key (writes APP_KEY into .env)
/usr/local/bin/php artisan key:generate

# Run the no-Redis deploy script
APP_DIR=/home/CPANEL_USER/ota bash deploy/cpanel/deploy-no-redis.sh
```

The script:
1. `composer install --no-dev`
2. `npm ci && npm run build`
3. `php artisan down`
4. `php artisan migrate --force`
5. `php artisan storage:link`
6. Caches config/routes/views
7. Runs `ota:security-audit-check`
8. `php artisan up`

If your cPanel account has **no Node/npm**, build the assets on your local
machine with `npm run build` and upload the generated `public/build/` folder
plus `public/.vite/` (if present) to the server.

## 6. Fix permissions

```bash
APP_DIR=/home/CPANEL_USER/ota CPANEL_USER=CPANEL_USER \
  bash deploy/cpanel/setup-permissions.sh
```

Ensure `storage/` and `bootstrap/cache/` are writable by the web user.

## 7. Add cron jobs

**cPanel → Cron Jobs** → paste the contents of `deploy/cpanel/cron-no-redis.txt`
(replacing `CPANEL_USER` with your real cPanel username). This runs the scheduler
and a short database queue worker every minute.

## 8. Enable SSL

**cPanel → SSL/TLS → AutoSSL** (or Let's Encrypt) → issue a certificate for the
subdomain. `APP_URL` must be `https://` and `FORCE_HTTPS=true`.

## 9. Verify

```bash
php artisan about
php artisan route:list
php artisan ota:security-audit-check
```

Then visit:

```
https://ota.yourdomain.com
https://ota.yourdomain.com/admin
https://ota.yourdomain.com/flights/search
https://ota.yourdomain.com/manage-booking
https://ota.yourdomain.com/health/secure
```

## 10. Updating later

```bash
cd /home/CPANEL_USER/ota
git pull
APP_DIR=/home/CPANEL_USER/ota bash deploy/cpanel/deploy-no-redis.sh
```

## Notes & caveats

- The database queue polled once a minute is fine for UAT traffic. For high
  volume, run a persistent `php artisan queue:work` under Supervisor (still no
  Redis needed).
- Broadcasting/realtime chat is non-functional in this mode. Functionality that
  only *sends* events is unaffected; only the live client-side push is dropped.
- Keep Stripe and Duffel in mock/sandbox until payment/provider production
  approvals are complete.
