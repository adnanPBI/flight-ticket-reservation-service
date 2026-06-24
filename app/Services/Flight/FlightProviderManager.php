<?php

namespace App\Services\Flight;

use App\Contracts\FlightProviderInterface;
use App\Enums\FlightProvider;
use InvalidArgumentException;

class FlightProviderManager
{
    public function __construct(
        private readonly DuffelService $duffel,
        private readonly AmadeusService $amadeus,
    ) {}

    public function provider(?FlightProvider $provider = null): FlightProviderInterface
    {
        $provider ??= FlightProvider::from(config('flight.default_provider', FlightProvider::Duffel->value));

        return match ($provider) {
            FlightProvider::Duffel => $this->duffel,
            FlightProvider::Amadeus => $this->amadeus,
            default => throw new InvalidArgumentException('Unsupported flight provider: '.$provider->value),
        };
    }
}
