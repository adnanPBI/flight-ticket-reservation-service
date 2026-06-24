<?php

namespace App\Data;

final readonly class FlightSegmentData
{
    public function __construct(
        public string $origin,
        public string $destination,
        public ?string $departureAt,
        public ?string $arrivalAt,
        public ?string $marketingCarrierCode = null,
        public ?string $marketingCarrierName = null,
        public ?string $flightNumber = null,
        public ?string $aircraftName = null,
        public ?int $durationMinutes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_at' => $this->departureAt,
            'arrival_at' => $this->arrivalAt,
            'marketing_carrier_code' => $this->marketingCarrierCode,
            'marketing_carrier_name' => $this->marketingCarrierName,
            'flight_number' => $this->flightNumber,
            'aircraft_name' => $this->aircraftName,
            'duration_minutes' => $this->durationMinutes,
        ];
    }
}
