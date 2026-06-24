<?php

namespace App\Services\Flight;

use App\Models\Booking;
use App\Models\FlightProviderLog;
use App\Models\FlightSearch;
use App\Services\Security\SensitiveDataMasker;
use Illuminate\Support\Str;

class FlightProviderLogger
{
    public function __construct(private readonly SensitiveDataMasker $masker) {}

    /**
     * @param array<string, mixed>|null $requestPayload
     * @param array<string, mixed>|null $responsePayload
     */
    public function log(
        string $provider,
        string $direction,
        ?string $endpoint = null,
        ?string $method = null,
        ?int $statusCode = null,
        ?array $requestPayload = null,
        ?array $responsePayload = null,
        ?string $errorMessage = null,
        ?int $durationMs = null,
        ?Booking $booking = null,
        ?FlightSearch $flightSearch = null,
    ): FlightProviderLog {
        return FlightProviderLog::query()->create([
            'provider' => $provider,
            'direction' => $direction,
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'correlation_id' => (string) Str::uuid(),
            'booking_id' => $booking?->id,
            'flight_search_id' => $flightSearch?->id,
            'request_payload' => $requestPayload ? $this->masker->maskArray($requestPayload) : null,
            'response_payload' => $responsePayload ? $this->masker->maskArray($responsePayload) : null,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }
}
