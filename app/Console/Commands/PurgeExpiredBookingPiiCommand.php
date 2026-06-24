<?php

namespace App\Console\Commands;

use App\Actions\Booking\RedactBookingPiiAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Console\Command;

class PurgeExpiredBookingPiiCommand extends Command
{
    protected $signature = 'ota:purge-expired-pii {--days= : Override the retention window} {--limit= : Override the batch size}';

    protected $description = 'Redact passenger PII on bookings in a terminal state past the retention window.';

    public function handle(RedactBookingPiiAction $action): int
    {
        $retentionDays = (int) ($this->option('days') ?: config('security.pii.retention_days', 90));
        $limit = (int) ($this->option('limit') ?: config('security.pii.purge_batch_size', 200));
        $cutoff = now()->subDays($retentionDays);

        $bookings = Booking::query()
            ->whereIn('status', [
                BookingStatus::Ticketed->value,
                BookingStatus::BookingFailed->value,
                BookingStatus::Refunded->value,
                BookingStatus::Cancelled->value,
            ])
            ->whereNull('pii_redacted_at')
            ->where('updated_at', '<', $cutoff)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings due for PII redaction.');
            return self::SUCCESS;
        }

        $redacted = 0;
        foreach ($bookings as $booking) {
            try {
                $action->execute($booking);
                $redacted++;
            } catch (\Throwable $e) {
                report($e);
                $this->error("Failed to redact PII for booking {$booking->booking_reference}: {$e->getMessage()}");
            }
        }

        $this->info("Redacted PII for {$redacted}/{$bookings->count()} bookings (retention window: {$retentionDays} days).");

        return self::SUCCESS;
    }
}
