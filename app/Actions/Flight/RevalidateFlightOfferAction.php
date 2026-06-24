<?php

namespace App\Actions\Flight;

use App\Data\NormalizedFlightOfferData;
use App\Models\FlightOffer;
use App\Services\Flight\FlightProviderManager;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RevalidateFlightOfferAction
{
    public function __construct(private readonly FlightProviderManager $providers) {}

    public function execute(FlightOffer $flightOffer): FlightOffer
    {
        $flightOffer->loadMissing('search');

        if ($flightOffer->search?->expires_at && $flightOffer->search->expires_at->isPast()) {
            throw new DomainException('This flight search session has expired. Please search again.');
        }

        if ($flightOffer->expires_at && $flightOffer->expires_at->isPast()) {
            throw new DomainException('This flight offer has expired. Please search again.');
        }

        if (! (bool) config('flight.revalidation.enabled', true)) {
            return $flightOffer->fresh();
        }

        if (str_starts_with((string) $flightOffer->provider_offer_id, 'mock_')) {
            return $flightOffer->fresh();
        }

        $freshOffer = $this->providers
            ->provider($flightOffer->provider)
            ->getOffer((string) $flightOffer->provider_offer_id);

        return $this->syncFreshOffer($flightOffer, $freshOffer);
    }

    private function syncFreshOffer(FlightOffer $flightOffer, NormalizedFlightOfferData $freshOffer): FlightOffer
    {
        $oldAmount = (int) $flightOffer->total_amount_minor;
        $newAmount = (int) $freshOffer->totalAmountMinor;
        $oldCurrency = strtoupper((string) $flightOffer->currency);
        $newCurrency = strtoupper((string) $freshOffer->currency);

        return DB::transaction(function () use ($flightOffer, $freshOffer, $oldAmount, $newAmount, $oldCurrency, $newCurrency): FlightOffer {
            $flightOffer->forceFill([
                'airline_code' => $freshOffer->airlineCode,
                'airline_name' => $freshOffer->airlineName,
                'origin' => $freshOffer->origin,
                'destination' => $freshOffer->destination,
                'departure_at' => $freshOffer->departureAt,
                'arrival_at' => $freshOffer->arrivalAt,
                'duration_minutes' => $freshOffer->durationMinutes,
                'stops' => $freshOffer->stops,
                'cabin_class' => $freshOffer->cabinClass,
                'fare_brand' => $freshOffer->fareBrand,
                'baggage_summary' => $freshOffer->baggageSummary,
                'refundability' => $freshOffer->refundability,
                'currency' => $newCurrency,
                'base_amount_minor' => $freshOffer->baseAmountMinor,
                'tax_amount_minor' => $freshOffer->taxAmountMinor,
                'fee_amount_minor' => $freshOffer->feeAmountMinor,
                'markup_amount_minor' => $freshOffer->markupAmountMinor,
                'discount_amount_minor' => $freshOffer->discountAmountMinor,
                'total_amount_minor' => $newAmount,
                'expires_at' => $freshOffer->expiresAt ? Carbon::parse($freshOffer->expiresAt) : $flightOffer->expires_at,
                'normalized_payload' => $freshOffer->toArray(),
                'raw_payload' => $freshOffer->rawPayload,
            ])->save();

            if ($oldAmount !== $newAmount || $oldCurrency !== $newCurrency) {
                logger()->warning('Flight offer price changed during revalidation.', [
                    'flight_offer_id' => $flightOffer->id,
                    'provider' => $flightOffer->provider->value,
                    'provider_offer_id' => $flightOffer->provider_offer_id,
                    'old_currency' => $oldCurrency,
                    'new_currency' => $newCurrency,
                    'old_total_amount_minor' => $oldAmount,
                    'new_total_amount_minor' => $newAmount,
                ]);
            }

            return $flightOffer->refresh();
        });
    }
}
