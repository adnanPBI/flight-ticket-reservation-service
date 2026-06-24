<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProviderBookingStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 300;

    public function handle(): void
    {
        Booking::query()
            ->whereIn('status', [BookingStatus::BookingConfirmed->value, BookingStatus::TicketingPending->value])
            ->whereNotNull('provider_order_id')
            ->where(function ($query): void {
                $query->whereNull('ticketing_last_checked_at')
                    ->orWhere('ticketing_last_checked_at', '<', now()->subMinutes(15));
            })
            ->limit(25)
            ->get()
            ->each(fn (Booking $booking) => RetryTicketingJob::dispatch($booking->id));
    }
}
