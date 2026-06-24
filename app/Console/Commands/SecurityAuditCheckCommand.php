<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SecurityAuditCheckCommand extends Command
{
    protected $signature = 'ota:security-audit-check';

    protected $description = 'Run a lightweight production-readiness security checklist for the OTA application.';

    public function handle(): int
    {
        $checks = [
            'APP_DEBUG=false' => ! config('app.debug'),
            'APP_ENV=production' => app()->environment('production'),
            'APP_KEY configured' => filled(config('app.key')),
            'HTTPS forced' => (bool) config('security.force_https'),
            'Security headers enabled' => (bool) config('security.headers.enabled'),
            'Sensitive data masking enabled' => (bool) config('security.masking.enabled'),
            'Stripe mock mode reviewed' => config('stripe.mock_mode') || filled(config('stripe.secret')),
            'Duffel token or mock mode reviewed' => config('flight.mock_mode') || filled(config('flight.providers.duffel.access_token')),
            'Storage is writable' => File::isWritable(storage_path()),
            'Bootstrap cache is writable' => File::isWritable(base_path('bootstrap/cache')),
        ];

        $failed = [];

        foreach ($checks as $label => $passed) {
            $this->line(($passed ? '<info>PASS</info>' : '<error>FAIL</error>') . ' - ' . $label);

            if (! $passed) {
                $failed[] = $label;
            }
        }

        if ($failed !== []) {
            $this->warn('Security audit check completed with warnings. Fix these before production:');
            foreach ($failed as $label) {
                $this->warn('- ' . $label);
            }
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
