<?php

namespace App\Jobs;

use App\Models\FlightOffer;
use App\Models\FlightSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireOldSearchSessionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function handle(): void
    {
        $limit = (int) config('cleanup.expired_search_limit', 500);

        $expiredSearchIds = FlightSearch::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->orderBy('expires_at')
            ->limit($limit)
            ->pluck('id');

        if ($expiredSearchIds->isEmpty()) {
            return;
        }

        $clearedOffers = FlightOffer::query()
            ->whereIn('flight_search_id', $expiredSearchIds)
            ->whereDoesntHave('bookings')
            ->whereNotNull('raw_payload')
            ->update(['raw_payload' => null]);

        Log::info('Expired old flight search sessions.', [
            'search_count' => $expiredSearchIds->count(),
            'offer_raw_payloads_cleared' => $clearedOffers,
        ]);
    }
}
