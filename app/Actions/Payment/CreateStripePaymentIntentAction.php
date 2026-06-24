<?php

namespace App\Actions\Payment;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Booking\BookingStateMachine;
use App\Services\Payment\PaymentStateMachine;
use App\Services\Payment\StripePaymentService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateStripePaymentIntentAction
{
    public function __construct(
        private readonly BookingStateMachine $bookingStateMachine,
        private readonly PaymentStateMachine $paymentStateMachine,
        private readonly StripePaymentService $stripePaymentService,
    ) {}

    public function execute(Booking $booking): array
    {
        [$booking, $payment] = DB::transaction(function () use ($booking): array {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($booking->status, [BookingStatus::PassengerDetailsAdded, BookingStatus::PaymentPending], true)) {
                throw new RuntimeException('Payment can be prepared only after passenger details are saved.');
            }

            if ($booking->offer_expires_at && $booking->offer_expires_at->isPast()) {
                throw new RuntimeException('This fare offer has expired. Please search again before payment.');
            }

            if ($booking->status === BookingStatus::PassengerDetailsAdded) {
                $booking = $this->bookingStateMachine->transition(
                    booking: $booking,
                    nextStatus: BookingStatus::PaymentPending,
                    reason: 'Stripe PaymentIntent preparation started',
                    metadata: ['phase' => '6']
                );
            }

            $payment = $booking->payments()
                ->where('provider', 'stripe')
                ->whereIn('status', [
                    PaymentStatus::Created->value,
                    PaymentStatus::RequiresPaymentMethod->value,
                    PaymentStatus::RequiresAction->value,
                    PaymentStatus::Processing->value,
                    PaymentStatus::Succeeded->value,
                ])
                ->latest('id')
                ->first();

            if (! $payment) {
                $payment = Payment::query()->create([
                    'booking_id' => $booking->id,
                    'provider' => 'stripe',
                    'status' => PaymentStatus::Created,
                    'currency' => $booking->currency,
                    'amount_minor' => $booking->total_amount_minor,
                    'metadata' => [
                        'created_by' => 'CreateStripePaymentIntentAction',
                        'phase' => '6',
                    ],
                ]);
            }

            return [$booking->refresh(), $payment->refresh()];
        });

        if ($payment->status === PaymentStatus::Succeeded) {
            return $this->toPayload($booking, $payment, [
                'client_secret' => null,
                'mock_mode' => config('stripe.mock_mode'),
                'publishable_key' => config('stripe.key'),
            ]);
        }

        // External provider call intentionally happens outside the DB transaction.
        $intent = $payment->provider_payment_id
            ? $this->stripePaymentService->retrievePaymentIntent($payment->provider_payment_id)
            : $this->stripePaymentService->createPaymentIntent($booking, $payment);

        $payment = DB::transaction(function () use ($payment, $intent): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            $payment->forceFill([
                'provider_payment_id' => $intent['provider_payment_id'],
                'client_secret_last4' => substr((string) $intent['client_secret'], -4),
                'metadata' => array_filter([
                    ...($payment->metadata ?? []),
                    'mock_mode' => $intent['mock_mode'],
                    'safe_note' => Arr::get($intent, 'safe_note'),
                ]),
            ])->save();

            $mappedStatus = $intent['status'];
            if ($payment->status !== $mappedStatus && $payment->status->canTransitionTo($mappedStatus)) {
                return $this->paymentStateMachine->transition(
                    payment: $payment,
                    nextStatus: $mappedStatus,
                    reason: 'Stripe PaymentIntent created/retrieved',
                    metadata: ['provider_payment_id' => $intent['provider_payment_id'], 'mock_mode' => $intent['mock_mode']]
                );
            }

            return $payment->refresh();
        });

        return $this->toPayload($booking, $payment, $intent);
    }

    private function toPayload(Booking $booking, Payment $payment, array $intent): array
    {
        return [
            'payment' => [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'provider_payment_id' => $payment->provider_payment_id,
                'status' => $payment->status->value,
                'currency' => $payment->currency,
                'amount_minor' => $payment->amount_minor,
                'client_secret' => $intent['client_secret'] ?? null,
                'mock_mode' => (bool) ($intent['mock_mode'] ?? config('stripe.mock_mode')),
                'publishable_key' => $intent['publishable_key'] ?? config('stripe.key'),
                'safe_note' => $intent['safe_note'] ?? null,
            ],
            'booking' => [
                'reference' => $booking->booking_reference,
                'status' => $booking->refresh()->status->value,
            ],
            'mock_success_url' => route('flights.payments.mock-succeed', ['booking' => $booking->booking_reference, 'payment' => $payment->id]),
            'confirmation_url' => route('flights.confirmation', ['booking' => $booking->booking_reference]),
        ];
    }
}
