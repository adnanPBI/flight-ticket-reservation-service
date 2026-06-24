<?php

namespace App\Actions\Flight;

use App\Data\FlightSearchData;
use App\Data\FlightSearchResultData;
use App\Enums\FlightProvider;
use App\Models\FlightOffer;
use App\Models\FlightSearch;
use App\Services\Flight\FlightProviderManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreFlightSearchAction
{
    public function __construct(private readonly FlightProviderManager $providers) {}

    public function execute(FlightSearchData $data, ?int $userId = null, ?FlightProvider $provider = null): FlightSearch
    {
        $provider ??= FlightProvider::Duffel;
        $result = $this->providers->provider($provider)->search($data);

        return DB::transaction(function () use ($data, $userId, $provider, $result): FlightSearch {
            $flightSearch = FlightSearch::query()->create([
                'user_id' => $userId,
                'search_reference' => 'FS-'.Str::upper(Str::random(10)),
                'provider' => $provider,
                'origin' => strtoupper($data->origin),
                'destination' => strtoupper($data->destination),
                'departure_date' => $data->departureDate,
                'return_date' => $data->returnDate,
                'trip_type' => $data->tripType,
                'adult_count' => $data->adultCount,
                'child_count' => $data->childCount,
                'infant_count' => $data->infantCount,
                'cabin_class' => $data->cabinClass,
                'currency' => strtoupper($data->currency),
                'expires_at' => $result->expiresAt ?? now()->addMinutes((int) config('flight.search.offer_ttl_minutes', 45)),
                'raw_request' => $data->toArray(),
                'raw_response_summary' => [
                    'provider_request_id' => $result->providerRequestId,
                    'offer_count' => $result->count(),
                ],
            ]);

            $this->storeOffers($flightSearch, $result);

            return $flightSearch->load('offers');
        });
    }

    private function storeOffers(FlightSearch $flightSearch, FlightSearchResultData $result): void
    {
        foreach ($result->offers as $offerData) {
            FlightOffer::query()->updateOrCreate(
                [
                    'provider' => $offerData->provider,
                    'provider_offer_id' => $offerData->providerOfferId,
                ],
                $offerData->toDatabaseArray($flightSearch->id),
            );
        }
    }
}
