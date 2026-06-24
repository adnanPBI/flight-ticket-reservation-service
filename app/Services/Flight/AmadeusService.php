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

class AmadeusService implements FlightProviderInterface
{
    public function __construct(
        private readonly FlightSearchNormalizer $normalizer,
        private readonly FlightProviderLogger $logger,
    ) {}

    public function search(FlightSearchData $data): FlightSearchResultData
    {
        // Phase 4+ can replace this with real Amadeus auth/search.
        return $this->normalizer->mockResult($data, FlightProvider::Amadeus);
    }

    public function getOffer(string $providerOfferId): NormalizedFlightOfferData
    {
        $data = new FlightSearchData('DAC', 'CXB', now()->addDays(10)->toDateString(), null, 'one_way', 1, 0, 0, 'economy', 'BDT');

        return $this->normalizer->mockResult($data, FlightProvider::Amadeus)->offers[0];
    }

    public function createOrder(Booking $booking): ProviderOrderResultData
    {
        $booking->loadMissing(['passengers', 'payments']);

        if ($this->isMockOffer($booking)) {
            $rawPayload = [
                'mock' => true,
                'booking_reference' => $booking->booking_reference,
                'selected_offer' => $booking->provider_offer_id,
            ];

            $result = new ProviderOrderResultData(
                provider: FlightProvider::Amadeus->value,
                providerOrderId: 'mock_order_'.strtolower(str_replace('-', '', (string) $booking->booking_reference)),
                pnr: 'AMX'.substr(md5((string) $booking->booking_reference), 0, 8),
                ticketNumber: 'ATK'.str_pad((string) $booking->id, 8, '0', STR_PAD_LEFT),
                status: 'ticketed',
                rawPayload: $rawPayload,
            );

            $this->logger->log(
                FlightProvider::Amadeus->value,
                'mock',
                'ordering/flight-orders',
                'POST',
                200,
                ['booking_id' => $booking->id],
                $rawPayload,
                null,
                null,
                $booking,
            );

            return $result;
        }

        $message = 'Amadeus real order creation is not implemented (Phase 4 fallback).';

        $this->logger->log(
            FlightProvider::Amadeus->value,
            'outbound',
            'ordering/flight-orders',
            'POST',
            null,
            ['booking_id' => $booking->id],
            null,
            $message,
            null,
            $booking,
        );

        throw ProviderException::permanent($message);
    }

    public function retrieveOrder(string $providerOrderId): ProviderOrderResultData
    {
        if ($this->isMockOrderId($providerOrderId)) {
            return new ProviderOrderResultData(
                provider: FlightProvider::Amadeus->value,
                providerOrderId: $providerOrderId,
                pnr: 'AMX'.substr(md5($providerOrderId), 0, 6),
                ticketNumber: 'ATK'.substr(md5($providerOrderId), 0, 8),
                status: 'ticketed',
                rawPayload: ['mock' => true, 'provider_order_id' => $providerOrderId],
            );
        }

        throw ProviderException::permanent('Amadeus order retrieval is not implemented (Phase 4 fallback).');
    }

    public function cancelOrder(string $providerOrderId): ProviderCancellationResultData
    {
        if ($this->isMockOrderId($providerOrderId)) {
            return new ProviderCancellationResultData(
                FlightProvider::Amadeus->value,
                $providerOrderId,
                'cancelled',
                'mock_refund_'.$providerOrderId,
                ['mock' => true],
            );
        }

        throw ProviderException::permanent('Amadeus cancellation is not implemented (Phase 4 fallback).');
    }

    private function isMockOffer(Booking $booking): bool
    {
        return str_starts_with((string) $booking->provider_offer_id, 'mock_') || (bool) config('flight.mock_mode', true);
    }

    private function isMockOrderId(string $providerOrderId): bool
    {
        return str_starts_with($providerOrderId, 'mock_') || (bool) config('flight.mock_mode', true);
    }
}
