<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\Flight\ProviderException;
use App\Jobs\RetryTicketingJob;
use App\Jobs\SendBookingConfirmationJob;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use App\Services\Flight\FlightProviderManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class FinalizeProviderBookingAction
{
    public function __construct(
        private readonly BookingStateMachine $bookingStateMachine,
        private readonly FlightProviderManager $flightProviderManager,
        private readonly RecordProviderOrderAction $recordProviderOrderAction,
        private readonly MarkBookingFinalizationFailedAction $markBookingFinalizationFailedAction,
    ) {}

    public function execute(int $bookingId): Booking
    {
        $booking = Booking::query()
            ->with(['passengers', 'segments', 'priceBreakdowns', 'payments'])
            ->findOrFail($bookingId);

        if ($booking->provider_order_id && in_array($booking->status, [BookingStatus::BookingConfirmed, BookingStatus::TicketingPending, BookingStatus::Ticketed], true)) {
            return $booking;
        }

        $this->assertPaidBooking($booking);

        $booking = $this->moveIntoConfirmingIfNeeded($booking);

        if ($this->realFinalizationIsDisabled($booking)) {
            return $this->holdForManualReview($booking);
        }

        try {
            $provider = $this->flightProviderManager->provider($booking->provider);
            $orderResult = $provider->createOrder($booking->refresh()->load(['passengers', 'segments', 'priceBreakdowns', 'payments']));
            $booking = $this->recordProviderOrderAction->execute($booking, $orderResult);

            SendBookingConfirmationJob::dispatch($booking->id);

            if ($booking->status === BookingStatus::TicketingPending && (bool) config('flight.orders.ticketing_sync_enabled', true)) {
                RetryTicketingJob::dispatch($booking->id)->delay(now()->addMinutes(5));
            }

            return $booking;
        } catch (Throwable $exception) {
            if ($this->isTransient($exception)) {
                throw $exception;
            }

            report($exception);

            return $this->markBookingFinalizationFailedAction->execute(
                booking: $booking,
                reason: $exception,
                metadata: ['phase' => '7', 'job' => 'ConfirmBookingJob']
            );
        }
    }

    /**
     * Transient failures (network/5xx) must be retried by the queue worker, so
     * they are rethrown instead of being marked as a permanent booking failure.
     */
    private function isTransient(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if ($exception instanceof ProviderException) {
            return $exception->isRetryable();
        }

        for ($previous = $exception->getPrevious(); $previous instanceof Throwable; $previous = $previous->getPrevious()) {
            if ($previous instanceof ConnectionException || ($previous instanceof ProviderException && $previous->isRetryable())) {
                return true;
            }
        }

        return false;
    }

    private function assertPaidBooking(Booking $booking): void
    {
        $hasSucceededPayment = $booking->payments->contains(fn ($payment) => $payment->status === PaymentStatus::Succeeded)
            || $booking->payments()->where('status', PaymentStatus::Succeeded->value)->exists();

        if (! $hasSucceededPayment) {
            throw new RuntimeException('Booking cannot be finalized because it has no succeeded payment.');
        }
    }

    private function moveIntoConfirmingIfNeeded(Booking $booking): Booking
    {
        if ($booking->status === BookingStatus::PaymentSucceeded) {
            return $this->bookingStateMachine->transition(
                booking: $booking,
                nextStatus: BookingStatus::BookingConfirming,
                reason: 'Payment succeeded; starting provider order finalization.',
                metadata: ['phase' => '7']
            );
        }

        if (in_array($booking->status, [BookingStatus::BookingConfirming, BookingStatus::TicketingPending], true)) {
            return $booking;
        }

        throw new RuntimeException("Booking status {$booking->status->value} cannot be finalized.");
    }

    private function realFinalizationIsDisabled(Booking $booking): bool
    {
        $isMockBooking = str_starts_with((string) $booking->provider_offer_id, 'mock_') || (bool) config('flight.mock_mode', true);

        return ! $isMockBooking && ! (bool) config('flight.orders.real_finalization_enabled', false);
    }

    private function holdForManualReview(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            $booking->forceFill([
                'provider_order_status' => 'manual_review_required',
                'failure_reason' => 'Real provider order finalization is disabled by FLIGHT_REAL_ORDER_FINALIZATION=false.',
                'ticketing_last_checked_at' => now(),
            ])->save();

            if ($booking->status === BookingStatus::BookingConfirming) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::TicketingPending,
                    reason: 'Real provider order finalization is disabled; booking requires manual review.',
                    metadata: ['safety_gate' => 'FLIGHT_REAL_ORDER_FINALIZATION=false']
                );
            }

            return $booking->refresh();
        });
    }
}
