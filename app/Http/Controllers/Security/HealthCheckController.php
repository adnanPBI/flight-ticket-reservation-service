<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
            'database' => $this->databaseStatus(),
            'cache' => $this->cacheStatus(),
        ];

        if ($this->usesRedis()) {
            $checks['redis'] = $this->redisStatus();
        }

        $healthy = collect($checks)->every(fn ($status): bool => $status === 'ok');

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function databaseStatus(): string
    {
        try {
            DB::select('select 1');
            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function cacheStatus(): string
    {
        try {
            Cache::put('health-check', 'ok', 10);
            return Cache::get('health-check') === 'ok' ? 'ok' : 'failed';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function redisStatus(): string
    {
        try {
            Redis::connection()->ping();
            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function usesRedis(): bool
    {
        return in_array('redis', [
            (string) config('cache.default'),
            (string) config('queue.default'),
            (string) config('session.driver'),
        ], true);
    }
}
