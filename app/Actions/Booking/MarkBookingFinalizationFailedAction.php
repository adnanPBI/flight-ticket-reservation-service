<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Jobs\RefundFailedBookingJob;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use Illuminate\Support\Facades\DB;
use Throwable;

class MarkBookingFinalizationFailedAction
{
    public function __construct(private readonly BookingStateMachine $bookingStateMachine) {}

    /** @param array<string, mixed> $metadata */
    public function execute(Booking $booking, Throwable|string $reason, array $metadata = []): Booking
    {
        $message = is_string($reason) ? $reason : $reason->getMessage();

        return DB::transaction(function () use ($booking, $message, $metadata): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            $booking->forceFill([
                'failed_at' => now(),
                'failure_reason' => str($message)->limit(240)->toString(),
                'provider_order_status' => $booking->provider_order_status ?: 'failed',
                'provider_order_payload' => [
                    'failure' => true,
                    'message' => $message,
                    'metadata' => $metadata,
                ],
            ])->save();

            if ($booking->status->canTransitionTo(BookingStatus::BookingFailed)) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::BookingFailed,
                    reason: 'Provider order finalization failed.',
                    metadata: ['failure_reason' => $message, ...$metadata]
                );
            }

            if ((bool) config('flight.orders.auto_refund_failed_bookings', false)) {
                RefundFailedBookingJob::dispatch($booking->id);
            }

            return $booking->refresh();
        });
    }
}
