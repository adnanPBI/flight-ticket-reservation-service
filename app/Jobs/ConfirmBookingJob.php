<?php

namespace App\Jobs;

use App\Actions\Booking\FinalizeProviderBookingAction;
use App\Actions\Booking\MarkBookingFinalizationFailedAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConfirmBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public readonly int $bookingId) {}

    public function handle(FinalizeProviderBookingAction $action): void
    {
        $booking = $action->execute($this->bookingId);

        Log::info('Provider booking finalization completed/queued for review.', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'status' => $booking->status->value,
            'provider_order_id' => $booking->provider_order_id,
            'provider_order_status' => $booking->provider_order_status,
        ]);
    }

    /**
     * Reached only after every transient retry is exhausted. The booking is still
     * in a confirming/ticketing state (transient failures never mark it failed),
     * so this is the single place that finalises it as failed and triggers the
     * refund review path for captured payments.
     */
    public function failed(Throwable $exception): void
    {
        $booking = Booking::query()->find($this->bookingId);

        if (! $booking instanceof Booking) {
            return;
        }

        if (! in_array($booking->status, [BookingStatus::BookingConfirming, BookingStatus::TicketingPending], true)) {
            return;
        }

        report($exception);

        app(MarkBookingFinalizationFailedAction::class)->execute(
            booking: $booking,
            reason: $exception,
            metadata: ['phase' => '7', 'job' => 'ConfirmBookingJob', 'exhausted_retries' => true]
        );
    }
}
