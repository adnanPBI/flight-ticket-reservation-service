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

class RefundFailedBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 300;

    public function __construct(public readonly int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::query()->with('payments')->findOrFail($this->bookingId);

        if ($booking->status !== BookingStatus::BookingFailed) {
            return;
        }

        // Phase 7 does not auto-call Stripe refunds by default. That is safer until accounting rules are approved.
        Log::warning('RefundFailedBookingJob placeholder: manual refund review required.', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'failure_reason' => $booking->failure_reason,
            'payments' => $booking->payments->pluck('provider_payment_id')->all(),
        ]);
    }
}
