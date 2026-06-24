<?php

namespace App\Services\Flight;

use App\Contracts\FlightProviderInterface;
use App\Data\FlightSearchData;
use App\Data\FlightSearchResultData;
use App\Data\NormalizedFlightOfferData;
use App\Data\ProviderCancellationResultData;
use App\Data\ProviderOrderResultData;
use App\Enums\FlightProvider;
use App\Exceptions\Flight\ProviderException;
use App\Models\Booking;
use App\Models\BookingPassenger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DuffelService implements FlightProviderInterface
{
    public function __construct(
        private readonly FlightSearchNormalizer $normalizer,
        private readonly FlightProviderLogger $logger,
    ) {}

    public function search(FlightSearchData $data): FlightSearchResultData
    {
        if ($this->shouldUseMock()) {
            $result = $this->normalizer->mockResult($data, FlightProvider::Duffel);
            $this->logger->log(FlightProvider::Duffel->value, 'mock', 'offer_requests', 'POST', 200, $data->toArray(), $result->rawPayload);

            return $result;
        }

        $payload = $this->buildSearchPayload($data);
        $startedAt = microtime(true);

        try {
            $response = Http::withToken((string) config('flight.duffel.access_token'))
                ->withHeaders(['Duffel-Version' => (string) config('flight.duffel.version')])
                ->timeout((int) config('flight.duffel.timeout', 30))
                ->post(rtrim((string) config('flight.duffel.base_url'), '/').'/air/offer_requests?return_offers=true', $payload);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $responsePayload = $response->json() ?? [];

            $this->logger->log(
                FlightProvider::Duffel->value,
                'outbound',
                'air/offer_requests',
                'POST',
                $response->status(),
                $payload,
                $responsePayload,
                $response->failed() ? $response->body() : null,
                $durationMs,
            );

            if ($response->failed()) {
                throw new RuntimeException('Duffel search failed with status '.$response->status());
            }

            return $this->normalizer->fromDuffelOfferRequest($responsePayload, $data);
        } catch (ConnectionException $exception) {
            $this->logger->log(FlightProvider::Duffel->value, 'outbound', 'air/offer_requests', 'POST', null, $payload, null, $exception->getMessage());
            throw $exception;
        }
    }

    public function getOffer(string $providerOfferId): NormalizedFlightOfferData
    {
        if ($this->shouldUseMock() || str_starts_with($providerOfferId, 'mock_')) {
            $data = new FlightSearchData('DAC', 'CXB', now()->addDays(10)->toDateString(), null, 'one_way', 1, 0, 0, 'economy', 'BDT');
            $result = $this->normalizer->mockResult($data, FlightProvider::Duffel);

            return $result->offers[0];
        }

        $response = Http::withToken((string) config('flight.duffel.access_token'))
            ->withHeaders(['Duffel-Version' => (string) config('flight.duffel.version')])
            ->timeout((int) config('flight.duffel.timeout', 30))
            ->get(rtrim((string) config('flight.duffel.base_url'), '/').'/air/offers/'.$providerOfferId);

        $payload = $response->json() ?? [];
        $this->logger->log(FlightProvider::Duffel->value, 'outbound', 'air/offers/'.$providerOfferId, 'GET', $response->status(), null, $payload, $response->failed() ? $response->body() : null);

        if ($response->failed()) {
            throw new RuntimeException('Duffel offer retrieval failed with status '.$response->status());
        }

        $fallbackSearch = new FlightSearchData('DAC', 'CXB', now()->addDays(10)->toDateString(), null, 'one_way', 1, 0, 0, 'economy', 'BDT');

        return $this->normalizer->fromDuffelOffer((array) data_get($payload, 'data', []), $fallbackSearch);
    }

    public function createOrder(Booking $booking): ProviderOrderResultData
    {
        $booking->loadMissing(['passengers', 'payments']);

        if ($this->shouldUseMock() || str_starts_with((string) $booking->provider_offer_id, 'mock_')) {
            $rawPayload = [
                'mock' => true,
                'booking_reference' => $booking->booking_reference,
                'selected_offer' => $booking->provider_offer_id,
            ];

            $result = new ProviderOrderResultData(
                provider: FlightProvider::Duffel->value,
                providerOrderId: 'mock_order_'.strtolower(str_replace('-', '', (string) $booking->booking_reference)),
                pnr: 'PNR'.substr(md5((string) $booking->booking_reference), 0, 8),
                ticketNumber: 'TKT'.str_pad((string) $booking->id, 8, '0', STR_PAD_LEFT),
                status: 'ticketed',
                rawPayload: $rawPayload,
            );

            $this->logger->log(FlightProvider::Duffel->value, 'mock', 'air/orders', 'POST', 200, ['booking_id' => $booking->id], $rawPayload);

            return $result;
        }

        if (! (bool) config('flight.orders.real_finalization_enabled', false)) {
            throw ProviderException::permanent('Real Duffel order creation blocked by FLIGHT_REAL_ORDER_FINALIZATION=false.');
        }

        $payload = $this->buildOrderPayload($booking);
        $startedAt = microtime(true);

        try {
            $response = Http::withToken((string) config('flight.duffel.access_token'))
                ->withHeaders(['Duffel-Version' => (string) config('flight.duffel.version')])
                ->timeout((int) config('flight.duffel.timeout', 30))
                ->post(rtrim((string) config('flight.duffel.base_url'), '/').'/air/orders', $payload);
        } catch (ConnectionException $exception) {
            $this->logger->log(
                FlightProvider::Duffel->value,
                'outbound',
                'air/orders',
                'POST',
                null,
                $this->redactOrderPayload($payload),
                null,
                $exception->getMessage(),
            );

            throw ProviderException::transient('Duffel order creation failed: could not connect to the provider.', null, $exception);
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $responsePayload = $response->json() ?? [];

        $this->logger->log(
            FlightProvider::Duffel->value,
            'outbound',
            'air/orders',
            'POST',
            $response->status(),
            $this->redactOrderPayload($payload),
            $responsePayload,
            $response->failed() ? $response->body() : null,
            $durationMs,
        );

        if ($response->failed()) {
            $status = $response->status();
            $message = 'Duffel order creation failed with status '.$status.': '.str($response->body())->limit(300);

            throw $status >= 500
                ? ProviderException::transient($message, $status)
                : ProviderException::permanent($message, $status);
        }

        return $this->fromDuffelOrderPayload($responsePayload);
    }

    public function retrieveOrder(string $providerOrderId): ProviderOrderResultData
    {
        if ($this->shouldUseMock() || str_starts_with($providerOrderId, 'mock_')) {
            return new ProviderOrderResultData(
                provider: FlightProvider::Duffel->value,
                providerOrderId: $providerOrderId,
                pnr: 'PNR'.substr(md5($providerOrderId), 0, 6),
                ticketNumber: 'TKT'.substr(md5($providerOrderId), 0, 8),
                status: 'ticketed',
                rawPayload: ['mock' => true, 'provider_order_id' => $providerOrderId],
            );
        }

        $response = Http::withToken((string) config('flight.duffel.access_token'))
            ->withHeaders(['Duffel-Version' => (string) config('flight.duffel.version')])
            ->timeout((int) config('flight.duffel.timeout', 30))
            ->get(rtrim((string) config('flight.duffel.base_url'), '/').'/air/orders/'.$providerOrderId);

        $payload = $response->json() ?? [];
        $this->logger->log(FlightProvider::Duffel->value, 'outbound', 'air/orders/'.$providerOrderId, 'GET', $response->status(), null, $payload, $response->failed() ? $response->body() : null);

        if ($response->failed()) {
            throw new RuntimeException('Duffel order retrieval failed with status '.$response->status());
        }

        return $this->fromDuffelOrderPayload($payload, $providerOrderId);
    }

    public function cancelOrder(string $providerOrderId): ProviderCancellationResultData
    {
        if ($this->shouldUseMock() || str_starts_with($providerOrderId, 'mock_')) {
            return new ProviderCancellationResultData(FlightProvider::Duffel->value, $providerOrderId, 'cancelled', 'mock_refund_'.$providerOrderId, ['mock' => true]);
        }

        throw new RuntimeException('Duffel cancellation is intentionally left for v2/change-cancel workflow.');
    }

    /** @return array<string, mixed> */
    private function buildSearchPayload(FlightSearchData $data): array
    {
        $slices = [[
            'origin' => strtoupper($data->origin),
            'destination' => strtoupper($data->destination),
            'departure_date' => $data->departureDate,
        ]];

        if ($data->returnDate) {
            $slices[] = [
                'origin' => strtoupper($data->destination),
                'destination' => strtoupper($data->origin),
                'departure_date' => $data->returnDate,
            ];
        }

        return [
            'data' => [
                'slices' => $slices,
                'passengers' => $this->passengerPayload($data),
                'cabin_class' => $data->cabinClass,
                'max_connections' => 1,
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function passengerPayload(FlightSearchData $data): array
    {
        $passengers = [];

        for ($i = 0; $i < $data->adultCount; $i++) {
            $passengers[] = ['type' => 'adult'];
        }

        for ($i = 0; $i < $data->childCount; $i++) {
            $passengers[] = ['type' => 'child'];
        }

        for ($i = 0; $i < $data->infantCount; $i++) {
            $passengers[] = ['type' => 'infant_without_seat'];
        }

        return $passengers;
    }

    /** @return array<string, mixed> */
    private function buildOrderPayload(Booking $booking): array
    {
        return [
            'data' => [
                'type' => 'instant',
                'selected_offers' => [(string) $booking->provider_offer_id],
                'passengers' => $booking->passengers->map(fn (BookingPassenger $passenger) => $this->buildOrderPassengerPayload($booking, $passenger))->values()->all(),
                'payments' => [[
                    'type' => 'balance',
                    'amount' => $this->minorToDecimalString((int) $booking->total_amount_minor),
                    'currency' => strtoupper($booking->currency),
                ]],
                'metadata' => [
                    'internal_booking_reference' => $booking->booking_reference,
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function buildOrderPassengerPayload(Booking $booking, BookingPassenger $passenger): array
    {
        $payload = [
            'type' => $this->mapPassengerType($passenger->passenger_type),
            'title' => $passenger->title ? strtolower($passenger->title) : null,
            'given_name' => $passenger->first_name,
            'family_name' => $passenger->last_name,
            'born_on' => optional($passenger->date_of_birth)->toDateString() ?: (string) $passenger->date_of_birth,
            'email' => $booking->customer_email,
            'phone_number' => $booking->customer_phone,
        ];

        if ($passenger->gender) {
            $payload['gender'] = strtolower($passenger->gender);
        }

        if ($passenger->passport_number && $passenger->passport_expiry_date) {
            $payload['identity_documents'] = [[
                'type' => 'passport',
                'unique_identifier' => $passenger->passport_number,
                'expires_on' => optional($passenger->passport_expiry_date)->toDateString() ?: (string) $passenger->passport_expiry_date,
                'issuing_country_code' => strtoupper((string) ($passenger->nationality ?: 'BD')),
            ]];
        }

        return array_filter($payload, fn ($value) => $value !== null && $value !== '');
    }

    private function mapPassengerType(string $passengerType): string
    {
        return match ($passengerType) {
            'infant' => 'infant_without_seat',
            default => $passengerType,
        };
    }

    private function minorToDecimalString(int $amountMinor): string
    {
        return number_format($amountMinor / 100, 2, '.', '');
    }

    /** @param array<string, mixed> $payload */
    private function fromDuffelOrderPayload(array $payload, ?string $fallbackOrderId = null): ProviderOrderResultData
    {
        $data = (array) data_get($payload, 'data', []);

        return new ProviderOrderResultData(
            provider: FlightProvider::Duffel->value,
            providerOrderId: (string) data_get($data, 'id', $fallbackOrderId),
            pnr: data_get($data, 'booking_reference'),
            ticketNumber: $this->extractTicketNumber($data),
            status: (string) data_get($data, 'status', 'confirmed'),
            rawPayload: $payload,
        );
    }

    /** @param array<string, mixed> $data */
    private function extractTicketNumber(array $data): ?string
    {
        $documentNumber = data_get($data, 'documents.0.unique_identifier')
            ?: data_get($data, 'passengers.0.documents.0.unique_identifier')
            ?: data_get($data, 'available_actions.0.ticket_number');

        return $documentNumber ? (string) $documentNumber : null;
    }

    /** @param array<string, mixed> $payload */
    private function redactOrderPayload(array $payload): array
    {
        foreach (data_get($payload, 'data.passengers', []) as $index => $passenger) {
            if (data_get($passenger, 'identity_documents.0.unique_identifier')) {
                data_set($payload, "data.passengers.$index.identity_documents.0.unique_identifier", '***redacted***');
            }
        }

        return $payload;
    }

    private function shouldUseMock(): bool
    {
        return (bool) config('flight.mock_mode', true) || blank(config('flight.duffel.access_token'));
    }
}
