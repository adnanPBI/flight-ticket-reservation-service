<?php

namespace App\Data;

final readonly class NormalizedFlightOfferData
{
    /**
     * @param array<int, FlightSegmentData> $segments
     * @param array<string, mixed> $normalizedPayload
     * @param array<string, mixed> $rawPayload
     */
    public function __construct(
        public string $provider,
        public string $providerOfferId,
        public ?string $airlineCode,
        public ?string $airlineName,
        public string $origin,
        public string $destination,
        public ?string $departureAt,
        public ?string $arrivalAt,
        public ?int $durationMinutes,
        public int $stops,
        public string $cabinClass,
        public ?string $fareBrand,
        public ?string $baggageSummary,
        public ?string $refundability,
        public string $currency,
        public int $baseAmountMinor,
        public int $taxAmountMinor,
        public int $feeAmountMinor,
        public int $markupAmountMinor,
        public int $discountAmountMinor,
        public int $totalAmountMinor,
        public ?string $expiresAt,
        public array $segments = [],
        public array $normalizedPayload = [],
        public array $rawPayload = [],
    ) {}

    public function toDatabaseArray(int $flightSearchId): array
    {
        return [
            'flight_search_id' => $flightSearchId,
            'provider' => $this->provider,
            'provider_offer_id' => $this->providerOfferId,
            'airline_code' => $this->airlineCode,
            'airline_name' => $this->airlineName,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_at' => $this->departureAt,
            'arrival_at' => $this->arrivalAt,
            'duration_minutes' => $this->durationMinutes,
            'stops' => $this->stops,
            'cabin_class' => $this->cabinClass,
            'fare_brand' => $this->fareBrand,
            'baggage_summary' => $this->baggageSummary,
            'refundability' => $this->refundability,
            'currency' => $this->currency,
            'base_amount_minor' => $this->baseAmountMinor,
            'tax_amount_minor' => $this->taxAmountMinor,
            'fee_amount_minor' => $this->feeAmountMinor,
            'markup_amount_minor' => $this->markupAmountMinor,
            'discount_amount_minor' => $this->discountAmountMinor,
            'total_amount_minor' => $this->totalAmountMinor,
            'expires_at' => $this->expiresAt,
            'normalized_payload' => $this->toArray(),
            'raw_payload' => $this->rawPayload,
        ];
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'provider_offer_id' => $this->providerOfferId,
            'airline_code' => $this->airlineCode,
            'airline_name' => $this->airlineName,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_at' => $this->departureAt,
            'arrival_at' => $this->arrivalAt,
            'duration_minutes' => $this->durationMinutes,
            'stops' => $this->stops,
            'cabin_class' => $this->cabinClass,
            'fare_brand' => $this->fareBrand,
            'baggage_summary' => $this->baggageSummary,
            'refundability' => $this->refundability,
            'currency' => $this->currency,
            'base_amount_minor' => $this->baseAmountMinor,
            'tax_amount_minor' => $this->taxAmountMinor,
            'fee_amount_minor' => $this->feeAmountMinor,
            'markup_amount_minor' => $this->markupAmountMinor,
            'discount_amount_minor' => $this->discountAmountMinor,
            'total_amount_minor' => $this->totalAmountMinor,
            'expires_at' => $this->expiresAt,
            'segments' => array_map(fn (FlightSegmentData $segment): array => $segment->toArray(), $this->segments),
        ];
    }
}
