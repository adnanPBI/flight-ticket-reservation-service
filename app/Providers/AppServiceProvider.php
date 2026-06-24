<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind concrete services in later phases.
    }

    public function boot(): void
    {
        if (app()->environment('production') && config('security.force_https', true)) {
            URL::forceScheme('https');
        }

        Model::preventSilentlyDiscardingAttributes(! app()->environment('production'));

        $this->configureRateLimits();
    }

    private function configureRateLimits(): void
    {
        RateLimiter::for('flight-search', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.flight_search_per_minute', 30))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('checkout', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.checkout_per_minute', 20))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('payment', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.payment_per_minute', 20))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('payment-webhook', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.webhook_per_minute', 120))->by($request->ip());
        });

        RateLimiter::for('chat', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.chat_per_minute', 60))
                ->by($request->user()?->id ?: $request->cookie('ota_visitor_token') ?: $request->ip());
        });

        RateLimiter::for('manage-booking', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.manage_booking_per_minute', 10))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.auth_per_minute', 5))
                ->by(mb_strtolower((string) ($request->input('email') ?? '')).'|'.$request->ip());
        });

        RateLimiter::for('admin-sensitive', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limits.admin_sensitive_per_minute', 30))
                ->by(auth('admin')->id() ?: $request->ip());
        });
    }
}
