<?php

namespace App\Jobs;

use App\Models\FlightOffer;
use App\Models\FlightProviderLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClearOldFlightOfferPayloadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function handle(): void
    {
        $offerCutoff = now()->subDays((int) config('cleanup.raw_offer_payload_retention_days', 14));
        $logCutoff = now()->subDays((int) config('cleanup.provider_log_payload_retention_days', 30));

        $offersCleared = FlightOffer::query()
            ->where('created_at', '<', $offerCutoff)
            ->whereDoesntHave('bookings')
            ->whereNotNull('raw_payload')
            ->update(['raw_payload' => null]);

        $providerLogsCleared = FlightProviderLog::query()
            ->where('created_at', '<', $logCutoff)
            ->whereNull('booking_id')
            ->where(function ($query): void {
                $query->whereNotNull('request_payload')->orWhereNotNull('response_payload');
            })
            ->update(['request_payload' => null, 'response_payload' => null]);

        Log::info('Cleared old raw flight payloads.', [
            'offers_cleared' => $offersCleared,
            'provider_logs_cleared' => $providerLogsCleared,
        ]);
    }
}
