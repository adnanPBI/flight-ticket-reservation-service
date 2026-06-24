# cPanel VPS Deployment Guide — Phase 0–1

This stack should run on a cPanel VPS with root/WHM access. Shared cPanel without Redis/Supervisor access is not enough for the final OTA system.

## Required services

- PHP 8.3/8.4+
- Composer
- Node.js + npm
- MySQL 8+
- Redis server
- Supervisor or systemd
- SSL
- Cron

## cPanel temporary domain setup

1. Create temporary subdomain in cPanel/WHM.
2. Upload project outside public web root where possible.
3. Point document root to:

```txt
/path/to/project/public
```

4. Copy `.env.example` to `.env` and update DB/Redis/app URL.
5. Run deployment commands:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Cron

Add this cron entry:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Supervisor examples

### Horizon

```ini
[program:ota-horizon]
process_name=%(program_name)s
command=php /path/to/project/artisan horizon
autostart=true
autorestart=true
user=cpaneluser
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/horizon.log
stopwaitsecs=3600
```

### Reverb

```ini
[program:ota-reverb]
process_name=%(program_name)s
command=php /path/to/project/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=cpaneluser
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/reverb.log
stopwaitsecs=3600
```
