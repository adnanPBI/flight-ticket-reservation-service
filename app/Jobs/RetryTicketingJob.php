<?php

namespace App\Jobs;

use App\Actions\Booking\RecordProviderOrderAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Flight\FlightProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryTicketingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [300, 900, 1800];

    public function __construct(public readonly int $bookingId) {}

    public function handle(FlightProviderManager $providerManager, RecordProviderOrderAction $recordProviderOrderAction): void
    {
        if (! (bool) config('flight.orders.ticketing_sync_enabled', true)) {
            return;
        }

        $booking = Booking::query()->findOrFail($this->bookingId);

        if (! $booking->provider_order_id) {
            Log::warning('RetryTicketingJob skipped because provider_order_id is missing.', ['booking_id' => $booking->id]);
            return;
        }

        if (! in_array($booking->status, [BookingStatus::BookingConfirmed, BookingStatus::TicketingPending], true)) {
            return;
        }

        $provider = $providerManager->provider($booking->provider);
        $orderResult = $provider->retrieveOrder($booking->provider_order_id);

        $booking->forceFill([
            'ticketing_last_checked_at' => now(),
            'ticketing_retry_count' => $booking->ticketing_retry_count + 1,
        ])->save();

        $recordProviderOrderAction->execute($booking, $orderResult);
    }
}
