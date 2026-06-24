<?php

namespace App\Data;

final readonly class FlightSearchResultData
{
    /**
     * @param array<int, NormalizedFlightOfferData> $offers
     * @param array<string, mixed> $rawPayload
     */
    public function __construct(
        public string $provider,
        public string $providerRequestId,
        public array $offers,
        public array $rawPayload = [],
        public ?string $expiresAt = null,
    ) {}

    public function count(): int
    {
        return count($this->offers);
    }
}
