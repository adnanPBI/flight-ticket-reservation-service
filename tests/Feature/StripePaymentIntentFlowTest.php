<?php

namespace Tests\Feature;

use App\Actions\Payment\CreateStripePaymentIntentAction;
use App\Actions\Payment\MarkPaymentSucceededAction;
use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripePaymentIntentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_mock_payment_intent_and_moves_booking_to_payment_pending(): void
    {
        config(['stripe.mock_mode' => true]);

        $booking = Booking::query()->create([
            'booking_reference' => 'BK-PAY123456',
            'status' => BookingStatus::PassengerDetailsAdded,
            'provider' => FlightProvider::Duffel,
            'provider_offer_id' => 'mock_offer_payment',
            'currency' => 'USD',
            'total_amount_minor' => 125000,
            'offer_expires_at' => now()->addMinutes(30),
            'customer_email' => 'payer@example.com',
        ]);

        $payload = app(CreateStripePaymentIntentAction::class)->execute($booking);

        $this->assertTrue($payload['payment']['mock_mode']);
        $this->assertStringStartsWith('pi_mock_', $payload['payment']['provider_payment_id']);
        $this->assertSame(PaymentStatus::RequiresPaymentMethod->value, $payload['payment']['status']);
        $this->assertSame(BookingStatus::PaymentPending, $booking->refresh()->status);
    }

    public function test_it_marks_mock_payment_succeeded_without_dispatching_provider_booking(): void
    {
        config(['stripe.mock_mode' => true, 'stripe.dispatch_booking_confirmation_job' => false]);

        $booking = Booking::query()->create([
            'booking_reference' => 'BK-SUCCESS123',
            'status' => BookingStatus::PassengerDetailsAdded,
            'provider' => FlightProvider::Duffel,
            'provider_offer_id' => 'mock_offer_success',
            'currency' => 'USD',
            'total_amount_minor' => 125000,
            'offer_expires_at' => now()->addMinutes(30),
            'customer_email' => 'payer@example.com',
        ]);

        $payload = app(CreateStripePaymentIntentAction::class)->execute($booking);
        $payment = $booking->payments()->firstOrFail();

        app(MarkPaymentSucceededAction::class)->execute($payment, 'evt_mock_test_success');

        $this->assertSame(PaymentStatus::Succeeded, $payment->refresh()->status);
        $this->assertSame(BookingStatus::PaymentSucceeded, $booking->refresh()->status);
        $this->assertSame(1, $payment->events()->where('provider_event_id', 'evt_mock_test_success')->count());
        $this->assertNotEmpty($payload['mock_success_url']);
    }
}
