<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HandoverCheckCommand extends Command
{
    protected $signature = 'ota:handover-check {--json : Output JSON instead of a table}';

    protected $description = 'Run a practical pre-handover readiness check for the OTA build.';

    public function handle(): int
    {
        $checks = [
            'app_key' => $this->checkAppKey(),
            'database' => $this->checkDatabase(),
            'redis_cache' => $this->checkCache(),
            'required_tables' => $this->checkRequiredTables(),
            'stripe_safety' => $this->checkStripeSafety(),
            'flight_safety' => $this->checkFlightSafety(),
            'storage_link' => $this->checkStorageLink(),
            'queue_connection' => $this->checkQueueConnection(),
            'broadcast_connection' => $this->checkBroadcastConnection(),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($checks, JSON_PRETTY_PRINT));
            return collect($checks)->every(fn ($check) => $check['ok']) ? self::SUCCESS : self::FAILURE;
        }

        $this->table(['Check', 'Status', 'Detail'], collect($checks)->map(fn ($item, $key) => [
            $key,
            $item['ok'] ? 'PASS' : 'WARN',
            $item['detail'],
        ])->all());

        $this->warn('WARN does not always mean broken. Resolve production warnings before client handover.');

        return collect($checks)->every(fn ($check) => $check['ok']) ? self::SUCCESS : self::FAILURE;
    }

    private function checkAppKey(): array
    {
        $key = (string) config('app.key');

        return [
            'ok' => str_starts_with($key, 'base64:') && strlen($key) > 20,
            'detail' => $key ? 'APP_KEY is set.' : 'APP_KEY is missing. Run php artisan key:generate.',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->select('select 1');
            return ['ok' => true, 'detail' => 'Database connection works.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'Database connection failed: '.$e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('ota_handover_check', now()->toIso8601String(), 10);
            return ['ok' => Cache::has('ota_handover_check'), 'detail' => 'Cache write/read completed.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'Cache check failed: '.$e->getMessage()];
        }
    }

    private function checkRequiredTables(): array
    {
        $tables = [
            'users', 'admin_users', 'flight_searches', 'flight_offers', 'bookings',
            'booking_passengers', 'payments', 'payment_events', 'booking_events',
            'flight_provider_logs', 'chat_conversations', 'chat_messages', 'audit_logs',
        ];

        $missing = collect($tables)->reject(fn ($table) => Schema::hasTable($table))->values();

        return [
            'ok' => $missing->isEmpty(),
            'detail' => $missing->isEmpty() ? 'Required OTA tables exist.' : 'Missing tables: '.$missing->implode(', '),
        ];
    }

    private function checkStripeSafety(): array
    {
        if (app()->environment('production') && config('stripe.mock_mode')) {
            return ['ok' => false, 'detail' => 'Production is still in STRIPE_MOCK_MODE.'];
        }

        return ['ok' => true, 'detail' => config('stripe.mock_mode') ? 'Stripe mock mode enabled.' : 'Stripe real/test mode enabled.'];
    }

    private function checkFlightSafety(): array
    {
        if (app()->environment('production') && config('flight.mock_mode')) {
            return ['ok' => false, 'detail' => 'Production is still in FLIGHT_MOCK_MODE.'];
        }

        return ['ok' => true, 'detail' => config('flight.mock_mode') ? 'Flight mock mode enabled.' : 'Real flight provider mode enabled.'];
    }

    private function checkStorageLink(): array
    {
        $link = public_path('storage');

        return [
            'ok' => is_link($link) || is_dir($link),
            'detail' => (is_link($link) || is_dir($link)) ? 'public/storage exists.' : 'Run php artisan storage:link.',
        ];
    }

    private function checkQueueConnection(): array
    {
        return [
            'ok' => config('queue.default') === 'redis',
            'detail' => 'QUEUE_CONNECTION='.config('queue.default'),
        ];
    }

    private function checkBroadcastConnection(): array
    {
        return [
            'ok' => config('broadcasting.default') === 'reverb',
            'detail' => 'BROADCAST_CONNECTION='.config('broadcasting.default'),
        ];
    }
}
