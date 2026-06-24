<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPendingTicketingAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 300;

    public function handle(): void
    {
        $cutoff = now()->subMinutes((int) config('cleanup.pending_ticketing_alert_minutes', 60));

        $bookings = Booking::query()
            ->whereIn('status', [BookingStatus::TicketingPending->value, BookingStatus::BookingConfirmed->value])
            ->whereNotNull('provider_order_id')
            ->where(function ($query) use ($cutoff): void {
                $query->whereNull('ticketed_at')
                    ->where(function ($nested) use ($cutoff): void {
                        $nested->whereNull('ticketing_last_checked_at')
                            ->orWhere('ticketing_last_checked_at', '<', $cutoff);
                    });
            })
            ->limit(50)
            ->get(['id', 'booking_reference', 'status', 'provider_order_id', 'provider_order_status', 'ticketing_last_checked_at']);

        if ($bookings->isEmpty()) {
            return;
        }

        Log::warning('Bookings still need ticketing/admin review.', [
            'count' => $bookings->count(),
            'bookings' => $bookings->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'reference' => $booking->booking_reference,
                'status' => $booking->status->value,
                'provider_order_id' => $booking->provider_order_id,
                'provider_order_status' => $booking->provider_order_status,
                'ticketing_last_checked_at' => optional($booking->ticketing_last_checked_at)->toDateTimeString(),
            ])->all(),
        ]);
    }
}
