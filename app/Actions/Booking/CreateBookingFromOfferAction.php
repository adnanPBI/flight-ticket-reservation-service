<?php

namespace App\Actions\Booking;

use App\Actions\Flight\RevalidateFlightOfferAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\FlightOffer;
use App\Services\Booking\BookingStateMachine;
use App\Services\Pricing\PriceCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBookingFromOfferAction
{
    public function __construct(
        private readonly RevalidateFlightOfferAction $revalidateFlightOffer,
        private readonly BookingStateMachine $stateMachine,
        private readonly PriceCalculationService $priceCalculationService,
    ) {}

    public function execute(FlightOffer $flightOffer, ?int $userId = null): Booking
    {
        $flightOffer = $this->revalidateFlightOffer->execute($flightOffer);
        $quote = $this->priceCalculationService->quoteForOffer($flightOffer);

        return DB::transaction(function () use ($flightOffer, $userId, $quote): Booking {
            $booking = Booking::query()->create([
                'user_id' => $userId,
                'flight_search_id' => $flightOffer->flight_search_id,
                'flight_offer_id' => $flightOffer->id,
                'booking_reference' => $this->makeBookingReference(),
                'status' => BookingStatus::SearchCreated,
                'provider' => $flightOffer->provider,
                'provider_offer_id' => $flightOffer->provider_offer_id,
                'offer_expires_at' => $flightOffer->expires_at,
                ...$quote->toBookingAttributes(),
            ]);

            $this->snapshotSegments($booking, $flightOffer);
            $this->snapshotPriceBreakdown($booking, $quote->breakdown, 'initial_quote');

            $this->stateMachine->transition(
                booking: $booking,
                nextStatus: BookingStatus::OfferSelected,
                actorUserId: $userId,
                reason: 'Customer selected and revalidated fare offer.',
                metadata: [
                    'flight_offer_id' => $flightOffer->id,
                    'provider_offer_id' => $flightOffer->provider_offer_id,
                    'offer_expires_at' => optional($flightOffer->expires_at)->toIso8601String(),
                    'pricing' => $quote->toSnapshot(),
                ],
            );

            return $booking->refresh()->load(['segments', 'priceBreakdowns', 'offer', 'search']);
        });
    }

    private function makeBookingReference(): string
    {
        do {
            $reference = 'BK-'.Str::upper(Str::random(10));
        } while (Booking::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function snapshotSegments(Booking $booking, FlightOffer $flightOffer): void
    {
        $segments = $flightOffer->normalized_payload['segments'] ?? [];

        foreach ($segments as $index => $segment) {
            $booking->segments()->create([
                'segment_order' => $index + 1,
                'airline_code' => $segment['marketing_carrier_code'] ?? $flightOffer->airline_code,
                'flight_number' => $segment['flight_number'] ?? null,
                'aircraft' => $segment['aircraft_name'] ?? null,
                'origin' => $segment['origin'] ?? $flightOffer->origin,
                'destination' => $segment['destination'] ?? $flightOffer->destination,
                'departure_at' => $segment['departure_at'] ?? $flightOffer->departure_at,
                'arrival_at' => $segment['arrival_at'] ?? $flightOffer->arrival_at,
                'duration_minutes' => $segment['duration_minutes'] ?? $flightOffer->duration_minutes,
                'booking_class' => null,
                'cabin_class' => $flightOffer->cabin_class,
                'status' => 'selected',
            ]);
        }

        if ($booking->segments()->count() === 0) {
            $booking->segments()->create([
                'segment_order' => 1,
                'airline_code' => $flightOffer->airline_code,
                'flight_number' => null,
                'aircraft' => null,
                'origin' => $flightOffer->origin,
                'destination' => $flightOffer->destination,
                'departure_at' => $flightOffer->departure_at,
                'arrival_at' => $flightOffer->arrival_at,
                'duration_minutes' => $flightOffer->duration_minutes,
                'booking_class' => null,
                'cabin_class' => $flightOffer->cabin_class,
                'status' => 'selected',
            ]);
        }
    }

    /** @param list<array<string, mixed>> $breakdown */
    private function snapshotPriceBreakdown(Booking $booking, array $breakdown, string $source): void
    {
        foreach ($breakdown as $item) {
            $booking->priceBreakdowns()->create([
                'label' => $item['label'],
                'type' => $item['type'],
                'currency' => $booking->currency,
                'amount_minor' => (int) $item['amount_minor'],
                'metadata' => ['source' => $source],
            ]);
        }
    }
}
