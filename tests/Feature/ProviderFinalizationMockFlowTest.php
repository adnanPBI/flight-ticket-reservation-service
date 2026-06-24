<?php

namespace Tests\Feature;

use App\Actions\Booking\FinalizeProviderBookingAction;
use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingPassenger;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderFinalizationMockFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mock_booking_finalization_creates_provider_order_snapshot(): void
    {
        config()->set('flight.mock_mode', true);
        config()->set('flight.orders.real_finalization_enabled', false);

        $booking = Booking::query()->create([
            'booking_reference' => 'OTA-TEST-0001',
            'status' => BookingStatus::PaymentSucceeded,
            'provider' => FlightProvider::Duffel,
            'provider_offer_id' => 'mock_offer_123',
            'currency' => 'BDT',
            'total_amount_minor' => 1500000,
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+8801700000000',
        ]);

        BookingPassenger::query()->create([
            'booking_id' => $booking->id,
            'passenger_type' => 'adult',
            'title' => 'mr',
            'first_name' => 'Test',
            'last_name' => 'Passenger',
            'date_of_birth' => '1990-01-01',
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_mock_test',
            'status' => PaymentStatus::Succeeded,
            'currency' => 'BDT',
            'amount_minor' => 1500000,
            'paid_at' => now(),
        ]);

        app(FinalizeProviderBookingAction::class)->execute($booking->id);

        $booking->refresh();

        $this->assertSame(BookingStatus::Ticketed, $booking->status);
        $this->assertNotNull($booking->provider_order_id);
        $this->assertNotNull($booking->pnr);
        $this->assertNotNull($booking->ticket_number);
    }
}
