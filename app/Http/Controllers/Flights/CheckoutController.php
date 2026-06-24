<?php

namespace App\Http\Controllers\Flights;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Booking $booking, AuthorizeManageBookingAccessAction $access): Response
    {
        abort_unless($access->allows(request(), $booking), 403, 'You are not authorized to view this booking.');

        $booking->load(['passengers', 'segments', 'priceBreakdowns', 'payments' => fn ($query) => $query->latest('id')]);

        abort_if(
            ! in_array($booking->status, [BookingStatus::PassengerDetailsAdded, BookingStatus::PaymentPending, BookingStatus::PaymentSucceeded], true),
            409,
            'Checkout is available only after passenger details are saved.'
        );

        $payment = $booking->payments->first();

        return Inertia::render('Flights/Checkout', [
            'booking' => [
                'reference' => $booking->booking_reference,
                'status' => $booking->status->value,
                'currency' => $booking->currency,
                'total_amount_minor' => $booking->total_amount_minor,
                'markup_amount_minor' => $booking->markup_amount_minor,
                'discount_amount_minor' => $booking->discount_amount_minor,
                'applied_promo_code' => $booking->applied_promo_code,
                'pricing_locked_at' => optional($booking->pricing_locked_at)->toIso8601String(),
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'passenger_count' => $booking->passengers->count(),
                'passengers' => $booking->passengers->map(fn ($passenger) => [
                    'passenger_type' => $passenger->passenger_type,
                    'first_name' => $passenger->first_name,
                    'last_name' => $passenger->last_name,
                ])->values(),
                'price_breakdown' => $booking->priceBreakdowns->map(fn ($item) => [
                    'label' => $item->label,
                    'type' => $item->type,
                    'currency' => $item->currency,
                    'amount_minor' => $item->amount_minor,
                ])->values(),
            ],
            'payment' => $payment ? [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'provider_payment_id' => $payment->provider_payment_id,
                'status' => $payment->status->value,
                'currency' => $payment->currency,
                'amount_minor' => $payment->amount_minor,
                'mock_mode' => (bool) ($payment->metadata['mock_mode'] ?? config('stripe.mock_mode')),
            ] : null,
            'paymentConfig' => [
                'mock_mode' => (bool) config('stripe.mock_mode'),
                'promo_apply_url' => route('flights.promo-code.apply', ['booking' => $booking->booking_reference]),
                'intent_url' => route('flights.payments.intent', ['booking' => $booking->booking_reference]),
                'confirmation_url' => route('flights.confirmation', ['booking' => $booking->booking_reference]),
                'publishable_key' => config('stripe.key'),
                'phase_note' => 'Phase 9 pricing is locked server-side before PaymentIntent creation. Applying a promo cancels old unpaid intents and requires a new PaymentIntent.',
            ],
        ]);
    }
}
