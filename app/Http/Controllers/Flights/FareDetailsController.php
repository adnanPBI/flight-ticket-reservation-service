<?php

namespace App\Http\Controllers\Flights;

use App\Http\Controllers\Controller;
use App\Models\FlightOffer;
use Inertia\Inertia;
use Inertia\Response;

class FareDetailsController extends Controller
{
    public function show(FlightOffer $flightOffer): Response
    {
        return Inertia::render('Flights/FareDetails', [
            'offer' => [
                'id' => $flightOffer->id,
                'airline_code' => $flightOffer->airline_code,
                'airline_name' => $flightOffer->airline_name,
                'origin' => $flightOffer->origin,
                'destination' => $flightOffer->destination,
                'departure_at' => optional($flightOffer->departure_at)->toIso8601String(),
                'arrival_at' => optional($flightOffer->arrival_at)->toIso8601String(),
                'duration_minutes' => $flightOffer->duration_minutes,
                'stops' => $flightOffer->stops,
                'fare_brand' => $flightOffer->fare_brand,
                'baggage_summary' => $flightOffer->baggage_summary,
                'refundability' => $flightOffer->refundability,
                'currency' => $flightOffer->currency,
                'base_amount_minor' => $flightOffer->base_amount_minor,
                'tax_amount_minor' => $flightOffer->tax_amount_minor,
                'fee_amount_minor' => $flightOffer->fee_amount_minor,
                'markup_amount_minor' => $flightOffer->markup_amount_minor,
                'discount_amount_minor' => $flightOffer->discount_amount_minor,
                'total_amount_minor' => $flightOffer->total_amount_minor,
                'expires_at' => optional($flightOffer->expires_at)->toIso8601String(),
                'segments' => $flightOffer->normalized_payload['segments'] ?? [],
            ],
        ]);
    }
}
