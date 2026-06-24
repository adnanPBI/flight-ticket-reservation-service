<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use App\Models\Booking;
use App\Services\Booking\BookingStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_status_transition_creates_event(): void
    {
        $booking = Booking::query()->create([
            'booking_reference' => 'BK-TEST001',
            'status' => BookingStatus::SearchCreated,
            'provider' => FlightProvider::Duffel,
            'currency' => 'USD',
        ]);

        app(BookingStateMachine::class)->transition($booking, BookingStatus::OfferSelected, reason: 'Test transition');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::OfferSelected->value,
        ]);

        $this->assertDatabaseHas('booking_events', [
            'booking_id' => $booking->id,
            'from_status' => BookingStatus::SearchCreated->value,
            'to_status' => BookingStatus::OfferSelected->value,
        ]);
    }
}
