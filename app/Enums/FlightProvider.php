<?php

namespace App\Enums;

enum FlightProvider: string
{
    case Duffel = 'duffel';
    case Amadeus = 'amadeus';

    public function label(): string
    {
        return match ($this) {
            self::Duffel => 'Duffel',
            self::Amadeus => 'Amadeus',
        };
    }
}
