<?php

namespace App\Actions\Payment;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Jobs\ConfirmBookingJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Booking\BookingStateMachine;
use App\Services\Payment\PaymentStateMachine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MarkPaymentSucceededAction
{
    public function __construct(
        private readonly PaymentStateMachine $paymentStateMachine,
        private readonly BookingStateMachine $bookingStateMachine,
    ) {}

    public function execute(Payment $payment, ?string $providerEventId = null, array $metadata = []): Payment
    {
        if ($providerEventId && PaymentEvent::query()->where('provider_event_id', $providerEventId)->exists()) {
            return $payment->refresh();
        }

        return DB::transaction(function () use ($payment, $providerEventId, $metadata): Payment {
            $booking = Booking::query()->lockForUpdate()->find($payment->booking_id);

            if (! $booking instanceof Booking) {
                throw new RuntimeException('Payment has no booking.');
            }

            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status !== PaymentStatus::Succeeded) {
                if (! $payment->status->canTransitionTo(PaymentStatus::Succeeded)) {
                    throw new RuntimeException("Payment cannot move from {$payment->status->value} to succeeded.");
                }

                $payment = $this->paymentStateMachine->transition(
                    payment: $payment,
                    nextStatus: PaymentStatus::Succeeded,
                    reason: 'Stripe payment succeeded',
                    metadata: [
                        'provider_event_id' => $providerEventId,
                        ...$metadata,
                    ],
                    providerEventId: $providerEventId,
                );

                $payment->forceFill(['paid_at' => now()])->save();
            }

            if ($booking->status === BookingStatus::PaymentPending) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::PaymentSucceeded,
                    reason: 'Payment succeeded; provider order finalization is queued by Phase 7.',
                    metadata: [
                        'payment_id' => $payment->id,
                        'provider_payment_id' => $payment->provider_payment_id,
                        'phase' => '7',
                    ]
                );
            }

            if (config('stripe.dispatch_booking_confirmation_job')) {
                ConfirmBookingJob::dispatch($booking->id);
            }

            return $payment->refresh();
        });
    }
}
