# Phase 0–1 Local Check

Run after installing dependencies:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Expected:

- migrations pass
- seeders create sample airports/airlines/admin/support agent
- Vite build succeeds
- booking state-machine feature test passes
