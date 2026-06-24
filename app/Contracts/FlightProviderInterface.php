<?php

namespace App\Contracts;

use App\Data\FlightSearchData;
use App\Data\FlightSearchResultData;
use App\Data\NormalizedFlightOfferData;
use App\Data\ProviderCancellationResultData;
use App\Data\ProviderOrderResultData;
use App\Models\Booking;

interface FlightProviderInterface
{
    public function search(FlightSearchData $data): FlightSearchResultData;

    public function getOffer(string $providerOfferId): NormalizedFlightOfferData;

    public function createOrder(Booking $booking): ProviderOrderResultData;

    public function retrieveOrder(string $providerOrderId): ProviderOrderResultData;

    public function cancelOrder(string $providerOrderId): ProviderCancellationResultData;
}
