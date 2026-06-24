<?php

namespace Tests\Feature;

use App\Models\FlightOffer;
use App\Models\FlightSearch;
use App\Models\MarkupRule;
use App\Models\PromoCode;
use App\Services\Pricing\PriceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPromoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_quote_applies_markup_and_promo(): void
    {
        MarkupRule::query()->create([
            'name' => 'Test markup',
            'scope' => 'global',
            'calculation_type' => 'fixed',
            'value' => 10,
            'is_active' => true,
        ]);

        $promo = PromoCode::query()->create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $search = FlightSearch::query()->create([
            'search_reference' => 'SRCH-TEST',
            'provider' => 'duffel',
            'origin' => 'DAC',
            'destination' => 'CXB',
            'departure_date' => now()->addWeek()->toDateString(),
            'trip_type' => 'one_way',
            'adult_count' => 1,
            'child_count' => 0,
            'infant_count' => 0,
            'cabin_class' => 'economy',
            'currency' => 'USD',
        ]);

        $offer = FlightOffer::query()->create([
            'flight_search_id' => $search->id,
            'provider' => 'duffel',
            'provider_offer_id' => 'off_test_1',
            'airline_code' => 'BG',
            'origin' => 'DAC',
            'destination' => 'CXB',
            'currency' => 'USD',
            'base_amount_minor' => 10000,
            'tax_amount_minor' => 1000,
            'fee_amount_minor' => 0,
            'total_amount_minor' => 11000,
        ]);

        $quote = app(PriceCalculationService::class)->quoteForOffer($offer, $promo);

        $this->assertSame(1000, $quote->markupAmountMinor);
        $this->assertSame(1200, $quote->discountAmountMinor);
        $this->assertSame(10800, $quote->totalAmountMinor);
    }
}
