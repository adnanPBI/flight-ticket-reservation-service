<?php

namespace Tests\Feature;

use App\Actions\Booking\StorePassengerDetailsAction;
use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use App\Models\Booking;
use App\Models\FlightOffer;
use App\Models\FlightSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerDetailsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_passengers_and_moves_booking_to_passenger_details_added(): void
    {
        $search = FlightSearch::query()->create([
            'search_reference' => 'FS-PAX123',
            'provider' => FlightProvider::Duffel,
            'origin' => 'DAC',
            'destination' => 'CXB',
            'departure_date' => now()->addDays(10)->toDateString(),
            'trip_type' => 'one_way',
            'adult_count' => 1,
            'child_count' => 0,
            'infant_count' => 0,
            'cabin_class' => 'economy',
            'currency' => 'BDT',
            'expires_at' => now()->addMinutes(45),
        ]);

        $offer = FlightOffer::query()->create([
            'flight_search_id' => $search->id,
            'provider' => FlightProvider::Duffel,
            'provider_offer_id' => 'mock_duffel_pax_offer',
            'origin' => 'DAC',
            'destination' => 'CXB',
            'cabin_class' => 'economy',
            'currency' => 'BDT',
            'total_amount_minor' => 3550000,
            'expires_at' => now()->addMinutes(45),
        ]);

        $booking = Booking::query()->create([
            'flight_search_id' => $search->id,
            'flight_offer_id' => $offer->id,
            'booking_reference' => 'BK-PAX123456',
            'status' => BookingStatus::OfferSelected,
            'provider' => FlightProvider::Duffel,
            'provider_offer_id' => $offer->provider_offer_id,
            'currency' => 'BDT',
            'total_amount_minor' => 3550000,
            'offer_expires_at' => now()->addMinutes(45),
        ]);

        $booking = app(StorePassengerDetailsAction::class)->execute($booking, [
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+8801711111111',
            'passengers' => [[
                'passenger_type' => 'adult',
                'title' => 'Mr',
                'first_name' => 'Test',
                'last_name' => 'Passenger',
                'date_of_birth' => '1995-01-01',
                'gender' => 'male',
                'nationality' => 'BD',
                'passport_number' => null,
                'passport_expiry_date' => null,
            ]],
        ]);

        $this->assertSame(BookingStatus::PassengerDetailsAdded, $booking->status);
        $this->assertSame(1, $booking->passengers()->count());
        $this->assertSame('customer@example.com', $booking->customer_email);
    }
}
