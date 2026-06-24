<?php

namespace App\Data;

final readonly class FlightSearchData
{
    public function __construct(
        public string $origin,
        public string $destination,
        public string $departureDate,
        public ?string $returnDate,
        public string $tripType,
        public int $adultCount,
        public int $childCount,
        public int $infantCount,
        public string $cabinClass,
        public string $currency = 'USD',
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            origin: strtoupper((string) $payload['origin']),
            destination: strtoupper((string) $payload['destination']),
            departureDate: (string) $payload['departure_date'],
            returnDate: $payload['return_date'] ?? null,
            tripType: (string) $payload['trip_type'],
            adultCount: (int) ($payload['adult_count'] ?? 1),
            childCount: (int) ($payload['child_count'] ?? 0),
            infantCount: (int) ($payload['infant_count'] ?? 0),
            cabinClass: (string) ($payload['cabin_class'] ?? 'economy'),
            currency: strtoupper((string) ($payload['currency'] ?? 'USD')),
        );
    }

    public function totalPassengers(): int
    {
        return $this->adultCount + $this->childCount + $this->infantCount;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_date' => $this->departureDate,
            'return_date' => $this->returnDate,
            'trip_type' => $this->tripType,
            'adult_count' => $this->adultCount,
            'child_count' => $this->childCount,
            'infant_count' => $this->infantCount,
            'cabin_class' => $this->cabinClass,
            'currency' => $this->currency,
            'total_passengers' => $this->totalPassengers(),
        ];
    }
}
