<?php

namespace App\Services\Flight;

use App\Data\FlightSearchData;
use App\Data\FlightSearchResultData;
use App\Data\FlightSegmentData;
use App\Data\NormalizedFlightOfferData;
use App\Enums\FlightProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FlightSearchNormalizer
{
    public function mockResult(FlightSearchData $data, FlightProvider $provider = FlightProvider::Duffel): FlightSearchResultData
    {
        $departAt = Carbon::parse($data->departureDate.' 08:30:00');
        $offers = [];

        $airlines = [
            ['BG', 'Biman Bangladesh Airlines', 3350000, 0],
            ['QR', 'Qatar Airways', 5280000, 1],
            ['EK', 'Emirates', 6120000, 1],
            ['TK', 'Turkish Airlines', 5890000, 1],
        ];

        foreach ($airlines as $index => [$code, $name, $amount, $stops]) {
            $departure = $departAt->copy()->addHours($index * 2);
            $arrival = $departure->copy()->addMinutes($stops === 0 ? 70 : 420);
            $offerId = 'mock_'.$provider->value.'_'.Str::lower(Str::random(12));
            $segments = [
                new FlightSegmentData(
                    origin: strtoupper($data->origin),
                    destination: strtoupper($data->destination),
                    departureAt: $departure->toDateTimeString(),
                    arrivalAt: $arrival->toDateTimeString(),
                    marketingCarrierCode: $code,
                    marketingCarrierName: $name,
                    flightNumber: $code.' '.random_int(100, 999),
                    aircraftName: $stops === 0 ? 'Boeing 737' : 'Airbus A330',
                    durationMinutes: $stops === 0 ? 70 : 420,
                ),
            ];

            $tax = (int) round($amount * 0.11);
            $fee = 120000;
            $markup = 150000;
            $total = $amount + $tax + $fee + $markup;

            $offers[] = new NormalizedFlightOfferData(
                provider: $provider->value,
                providerOfferId: $offerId,
                airlineCode: $code,
                airlineName: $name,
                origin: strtoupper($data->origin),
                destination: strtoupper($data->destination),
                departureAt: $departure->toDateTimeString(),
                arrivalAt: $arrival->toDateTimeString(),
                durationMinutes: $stops === 0 ? 70 : 420,
                stops: $stops,
                cabinClass: $data->cabinClass,
                fareBrand: $stops === 0 ? 'Saver' : 'Flex',
                baggageSummary: $stops === 0 ? '20kg checked + 7kg cabin' : '30kg checked + 7kg cabin',
                refundability: $stops === 0 ? 'partially_refundable' : 'refundable_with_fee',
                currency: strtoupper($data->currency),
                baseAmountMinor: $amount,
                taxAmountMinor: $tax,
                feeAmountMinor: $fee,
                markupAmountMinor: $markup,
                discountAmountMinor: 0,
                totalAmountMinor: $total,
                expiresAt: now()->addMinutes((int) config('flight.search.offer_ttl_minutes', 45))->toDateTimeString(),
                segments: $segments,
                normalizedPayload: [],
                rawPayload: ['mock' => true],
            );
        }

        return new FlightSearchResultData(
            provider: $provider->value,
            providerRequestId: 'mock_request_'.Str::lower(Str::random(10)),
            offers: $offers,
            rawPayload: ['mock' => true, 'request' => $data],
            expiresAt: now()->addMinutes((int) config('flight.search.offer_ttl_minutes', 45))->toDateTimeString(),
        );
    }

    /** @param array<string, mixed> $payload */
    public function fromDuffelOfferRequest(array $payload, FlightSearchData $data): FlightSearchResultData
    {
        $offers = [];
        $rawOffers = Arr::get($payload, 'data.offers', Arr::get($payload, 'data', []));

        foreach ((array) $rawOffers as $offer) {
            if (! is_array($offer)) {
                continue;
            }

            $offers[] = $this->fromDuffelOffer($offer, $data);
        }

        return new FlightSearchResultData(
            provider: FlightProvider::Duffel->value,
            providerRequestId: (string) Arr::get($payload, 'data.id', 'duffel_request_'.Str::random(8)),
            offers: array_slice($offers, 0, (int) config('flight.search.max_results_for_mvp', 30)),
            rawPayload: $payload,
            expiresAt: now()->addMinutes((int) config('flight.search.offer_ttl_minutes', 45))->toDateTimeString(),
        );
    }

    /** @param array<string, mixed> $offer */
    public function fromDuffelOffer(array $offer, FlightSearchData $data): NormalizedFlightOfferData
    {
        $firstSlice = Arr::first((array) Arr::get($offer, 'slices', []));
        $segmentsRaw = (array) Arr::get($firstSlice, 'segments', []);
        $firstSegment = Arr::first($segmentsRaw) ?: [];
        $lastSegment = Arr::last($segmentsRaw) ?: $firstSegment;
        $owner = (array) Arr::get($offer, 'owner', []);
        $amountMinor = $this->decimalAmountToMinor((string) Arr::get($offer, 'total_amount', '0'));
        $taxMinor = $this->decimalAmountToMinor((string) Arr::get($offer, 'tax_amount', '0'));
        $baseMinor = max($amountMinor - $taxMinor, 0);
        $segments = [];

        foreach ($segmentsRaw as $segment) {
            if (! is_array($segment)) {
                continue;
            }

            $segments[] = new FlightSegmentData(
                origin: (string) Arr::get($segment, 'origin.iata_code', Arr::get($segment, 'origin', $data->origin)),
                destination: (string) Arr::get($segment, 'destination.iata_code', Arr::get($segment, 'destination', $data->destination)),
                departureAt: Arr::get($segment, 'departing_at'),
                arrivalAt: Arr::get($segment, 'arriving_at'),
                marketingCarrierCode: Arr::get($segment, 'marketing_carrier.iata_code'),
                marketingCarrierName: Arr::get($segment, 'marketing_carrier.name'),
                flightNumber: trim((string) Arr::get($segment, 'marketing_carrier_flight_number')),
                aircraftName: Arr::get($segment, 'aircraft.name'),
                durationMinutes: $this->durationToMinutes(Arr::get($segment, 'duration')),
            );
        }

        return new NormalizedFlightOfferData(
            provider: FlightProvider::Duffel->value,
            providerOfferId: (string) Arr::get($offer, 'id'),
            airlineCode: Arr::get($owner, 'iata_code'),
            airlineName: Arr::get($owner, 'name'),
            origin: (string) Arr::get($firstSegment, 'origin.iata_code', $data->origin),
            destination: (string) Arr::get($lastSegment, 'destination.iata_code', $data->destination),
            departureAt: Arr::get($firstSegment, 'departing_at'),
            arrivalAt: Arr::get($lastSegment, 'arriving_at'),
            durationMinutes: $this->durationToMinutes(Arr::get($firstSlice, 'duration')),
            stops: max(count($segmentsRaw) - 1, 0),
            cabinClass: $data->cabinClass,
            fareBrand: Arr::get($offer, 'fare_brand_name'),
            baggageSummary: $this->baggageSummary($offer),
            refundability: Arr::get($offer, 'conditions.refund_before_departure.allowed') ? 'refundable' : 'check_fare_rules',
            currency: (string) Arr::get($offer, 'total_currency', $data->currency),
            baseAmountMinor: $baseMinor,
            taxAmountMinor: $taxMinor,
            feeAmountMinor: 0,
            markupAmountMinor: 0,
            discountAmountMinor: 0,
            totalAmountMinor: $amountMinor,
            expiresAt: Arr::get($offer, 'expires_at', now()->addMinutes(45)->toDateTimeString()),
            segments: $segments,
            normalizedPayload: [],
            rawPayload: $offer,
        );
    }

    private function decimalAmountToMinor(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function durationToMinutes(mixed $duration): ?int
    {
        if (! is_string($duration) || $duration === '') {
            return null;
        }

        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches);

        if ($matches === []) {
            return null;
        }

        return ((int) ($matches[1] ?? 0)) * 60 + ((int) ($matches[2] ?? 0));
    }

    /** @param array<string, mixed> $offer */
    private function baggageSummary(array $offer): ?string
    {
        $services = Arr::get($offer, 'available_services', []);

        foreach ((array) $services as $service) {
            if (is_array($service) && str_contains(strtolower((string) Arr::get($service, 'type')), 'bag')) {
                return 'Baggage options available';
            }
        }

        return 'Check airline baggage rules';
    }
}
