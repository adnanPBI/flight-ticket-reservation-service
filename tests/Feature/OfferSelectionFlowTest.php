<?php

namespace Tests\Feature;

use App\Actions\Booking\CreateBookingFromOfferAction;
use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use App\Models\FlightOffer;
use App\Models\FlightSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferSelectionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_booking_snapshot_from_selected_offer(): void
    {
        config(['flight.mock_mode' => true]);

        $search = FlightSearch::query()->create([
            'search_reference' => 'FS-TEST123',
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
            'provider_offer_id' => 'mock_duffel_test_offer',
            'airline_code' => 'BG',
            'airline_name' => 'Biman Bangladesh Airlines',
            'origin' => 'DAC',
            'destination' => 'CXB',
            'departure_at' => now()->addDays(10),
            'arrival_at' => now()->addDays(10)->addMinutes(70),
            'duration_minutes' => 70,
            'stops' => 0,
            'cabin_class' => 'economy',
            'fare_brand' => 'Saver',
            'baggage_summary' => '20kg checked + 7kg cabin',
            'refundability' => 'partially_refundable',
            'currency' => 'BDT',
            'base_amount_minor' => 3000000,
            'tax_amount_minor' => 300000,
            'fee_amount_minor' => 100000,
            'markup_amount_minor' => 150000,
            'discount_amount_minor' => 0,
            'total_amount_minor' => 3550000,
            'expires_at' => now()->addMinutes(45),
            'normalized_payload' => [
                'segments' => [[
                    'origin' => 'DAC',
                    'destination' => 'CXB',
                    'departure_at' => now()->addDays(10)->toDateTimeString(),
                    'arrival_at' => now()->addDays(10)->addMinutes(70)->toDateTimeString(),
                    'marketing_carrier_code' => 'BG',
                    'flight_number' => 'BG 401',
                    'duration_minutes' => 70,
                ]],
            ],
        ]);

        $booking = app(CreateBookingFromOfferAction::class)->execute($offer);

        $this->assertSame(BookingStatus::OfferSelected, $booking->status);
        $this->assertSame(1, $booking->segments()->count());
        $this->assertSame(6, $booking->priceBreakdowns()->count());
        $this->assertDatabaseHas('booking_events', ['booking_id' => $booking->id, 'to_status' => BookingStatus::OfferSelected->value]);
    }
}
