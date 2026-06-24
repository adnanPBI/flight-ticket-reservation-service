# Phase 15 — Optional Docker Deployment

This phase adds a cloud/VPS-friendly Docker path. It is optional because the preferred deployment remains the Namecheap cPanel VPS path from Phase 14.

## Included files

```txt
Dockerfile
docker-compose.yml
.dockerignore
.env.docker.example
Makefile
docker/nginx/default.conf
docker/php/php.ini
docker/php/opcache.ini
docker/mysql/init.sql
docker/scripts/entrypoint.sh
deploy/docker/docker-deploy.sh
scripts/docker_smoke_test.sh
```

## Services

```txt
app        PHP-FPM Laravel app image
nginx      Public web server on port 8080
mysql      MySQL 8.4 local database
redis      Redis queue/cache/session backend
horizon    Laravel Horizon worker monitor
reverb     Laravel WebSocket server on port 8081
scheduler  Laravel scheduler worker
mailpit    Local email catcher on port 8025
```

## Local Docker start

```bash
cp .env.docker.example .env
docker compose build
docker compose up -d mysql redis mailpit
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --seed
docker compose up -d app nginx horizon reverb scheduler
```

Then open:

```txt
http://localhost:8080
http://localhost:8080/flights/search
http://localhost:8025
```

## Smoke test

```bash
BASE_URL=http://localhost:8080 bash scripts/docker_smoke_test.sh
```

## Important safety defaults

Docker local mode uses mock integrations by default:

```env
STRIPE_MOCK_MODE=true
FLIGHT_MOCK_MODE=true
FLIGHT_REAL_ORDER_FINALIZATION=false
```

This prevents accidental card charging or real airline order creation during Docker testing.

## Production Docker warning

For a real production Docker deployment:

1. Set real `APP_KEY`.
2. Set `APP_ENV=production` and `APP_DEBUG=false`.
3. Use production MySQL/Redis credentials.
4. Replace mock Stripe and flight settings only after provider approval.
5. Put Nginx behind HTTPS/load balancer.
6. Do not publish MySQL/Redis ports publicly.
7. Configure log retention and backups.

## Exit checklist

```txt
[ ] docker compose build completes
[ ] app container boots
[ ] nginx serves Laravel public directory
[ ] MySQL healthcheck passes
[ ] Redis is reachable
[ ] migrations run
[ ] npm build assets are visible
[ ] Horizon container runs
[ ] Reverb container runs
[ ] scheduler container runs
[ ] mock search → payment → booking path works
```
