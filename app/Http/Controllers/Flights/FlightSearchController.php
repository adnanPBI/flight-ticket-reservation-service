<?php

namespace App\Http\Controllers\Flights;

use App\Actions\Flight\StoreFlightSearchAction;
use App\Data\FlightSearchData;
use App\Enums\FlightProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\FlightSearchRequest;
use App\Models\FlightSearch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FlightSearchController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Flights/Search', [
            'defaults' => [
                'origin' => 'DAC',
                'destination' => 'CXB',
                'departure_date' => now()->addDays(7)->toDateString(),
                'return_date' => null,
                'trip_type' => 'one_way',
                'adult_count' => 1,
                'child_count' => 0,
                'infant_count' => 0,
                'cabin_class' => 'economy',
                'currency' => 'BDT',
                'provider' => config('flight.default_provider', 'duffel'),
            ],
        ]);
    }

    public function store(FlightSearchRequest $request, StoreFlightSearchAction $action): RedirectResponse
    {
        $data = FlightSearchData::fromArray($request->validated());
        $provider = FlightProvider::from($request->validated('provider', config('flight.default_provider', 'duffel')));
        $flightSearch = $action->execute($data, $request->user()?->id, $provider);

        return redirect()->route('flights.results', $flightSearch)
            ->with('success', 'Flight search completed. Offers are valid for a limited time.');
    }

    public function results(FlightSearch $flightSearch): Response
    {
        $flightSearch->load(['offers' => fn ($query) => $query->orderBy('total_amount_minor')]);

        return Inertia::render('Flights/Results', [
            'search' => [
                'id' => $flightSearch->id,
                'reference' => $flightSearch->search_reference,
                'origin' => $flightSearch->origin,
                'destination' => $flightSearch->destination,
                'departure_date' => optional($flightSearch->departure_date)->toDateString(),
                'return_date' => optional($flightSearch->return_date)->toDateString(),
                'trip_type' => $flightSearch->trip_type,
                'adult_count' => $flightSearch->adult_count,
                'child_count' => $flightSearch->child_count,
                'infant_count' => $flightSearch->infant_count,
                'cabin_class' => $flightSearch->cabin_class,
                'currency' => $flightSearch->currency,
                'expires_at' => optional($flightSearch->expires_at)->toIso8601String(),
            ],
            'offers' => $flightSearch->offers->map(fn ($offer) => [
                'id' => $offer->id,
                'provider' => $offer->provider->value,
                'provider_offer_id' => $offer->provider_offer_id,
                'airline_code' => $offer->airline_code,
                'airline_name' => $offer->airline_name,
                'origin' => $offer->origin,
                'destination' => $offer->destination,
                'departure_at' => optional($offer->departure_at)->toIso8601String(),
                'arrival_at' => optional($offer->arrival_at)->toIso8601String(),
                'duration_minutes' => $offer->duration_minutes,
                'stops' => $offer->stops,
                'cabin_class' => $offer->cabin_class,
                'fare_brand' => $offer->fare_brand,
                'baggage_summary' => $offer->baggage_summary,
                'refundability' => $offer->refundability,
                'currency' => $offer->currency,
                'total_amount_minor' => $offer->total_amount_minor,
                'expires_at' => optional($offer->expires_at)->toIso8601String(),
                'segments' => $offer->normalized_payload['segments'] ?? [],
            ])->values(),
        ]);
    }
}
