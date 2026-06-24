<?php

namespace App\Services\Pricing;

use App\Data\PriceQuoteData;
use App\Models\Booking;
use App\Models\FlightOffer;
use App\Models\MarkupRule;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use RuntimeException;

class PriceCalculationService
{
    public function quoteForOffer(FlightOffer $offer, ?PromoCode $promoCode = null): PriceQuoteData
    {
        $context = [
            'provider' => $offer->provider->value,
            'airline_code' => $offer->airline_code,
            'origin' => $offer->origin,
            'destination' => $offer->destination,
            'cabin_class' => $offer->cabin_class,
            'currency' => $offer->currency,
            'trip_type' => $offer->search?->trip_type,
            'adult_count' => $offer->search?->adult_count,
            'child_count' => $offer->search?->child_count,
            'infant_count' => $offer->search?->infant_count,
        ];

        return $this->quote(
            currency: $offer->currency,
            providerBaseAmountMinor: (int) $offer->base_amount_minor,
            taxAmountMinor: (int) $offer->tax_amount_minor,
            feeAmountMinor: (int) $offer->fee_amount_minor,
            context: $context,
            promoCode: $promoCode,
        );
    }

    public function quoteForBooking(Booking $booking, ?PromoCode $promoCode = null): PriceQuoteData
    {
        $booking->loadMissing(['offer.search']);

        $context = [
            'provider' => $booking->provider->value,
            'airline_code' => $booking->offer?->airline_code,
            'origin' => $booking->offer?->origin,
            'destination' => $booking->offer?->destination,
            'cabin_class' => $booking->offer?->cabin_class,
            'currency' => $booking->currency,
            'trip_type' => $booking->offer?->search?->trip_type,
            'adult_count' => $booking->offer?->search?->adult_count,
            'child_count' => $booking->offer?->search?->child_count,
            'infant_count' => $booking->offer?->search?->infant_count,
        ];

        return $this->quote(
            currency: $booking->currency,
            providerBaseAmountMinor: (int) $booking->provider_base_amount_minor,
            taxAmountMinor: (int) $booking->tax_amount_minor,
            feeAmountMinor: (int) $booking->fee_amount_minor,
            context: $context,
            promoCode: $promoCode,
        );
    }

    /** @param array<string, mixed> $context */
    public function quote(string $currency, int $providerBaseAmountMinor, int $taxAmountMinor, int $feeAmountMinor, array $context, ?PromoCode $promoCode = null): PriceQuoteData
    {
        $subtotalBeforeCommercial = max(0, $providerBaseAmountMinor + $taxAmountMinor + $feeAmountMinor);
        $markupAmountMinor = 0;
        $appliedRules = [];

        $rules = MarkupRule::query()
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            if (! $this->matchesRule($rule, $context, $currency)) {
                continue;
            }

            $amount = $this->calculateRuleAmount($rule, $subtotalBeforeCommercial, $context);
            if ($amount <= 0) {
                continue;
            }

            $markupAmountMinor += $amount;
            $appliedRules[] = [
                'id' => $rule->id,
                'name' => $rule->name,
                'scope' => $rule->scope,
                'calculation_type' => $rule->calculation_type,
                'value' => (string) $rule->value,
                'amount_minor' => $amount,
            ];
        }

        $subtotalAfterMarkup = $subtotalBeforeCommercial + $markupAmountMinor;
        $discountAmountMinor = $promoCode ? $this->calculatePromoDiscount($promoCode, $subtotalAfterMarkup, $currency) : 0;
        $totalAmountMinor = max((int) config('pricing.minimum_total_minor', 100), $subtotalAfterMarkup - $discountAmountMinor);

        return new PriceQuoteData(
            currency: $currency,
            providerBaseAmountMinor: $providerBaseAmountMinor,
            taxAmountMinor: $taxAmountMinor,
            feeAmountMinor: $feeAmountMinor,
            markupAmountMinor: $markupAmountMinor,
            discountAmountMinor: $discountAmountMinor,
            totalAmountMinor: $totalAmountMinor,
            breakdown: [
                ['label' => 'Base fare', 'type' => 'base', 'amount_minor' => $providerBaseAmountMinor],
                ['label' => 'Taxes', 'type' => 'tax', 'amount_minor' => $taxAmountMinor],
                ['label' => 'Fees', 'type' => 'fee', 'amount_minor' => $feeAmountMinor],
                ['label' => 'Platform markup', 'type' => 'markup', 'amount_minor' => $markupAmountMinor],
                ['label' => 'Promo discount', 'type' => 'discount', 'amount_minor' => -1 * $discountAmountMinor],
                ['label' => 'Total', 'type' => 'total', 'amount_minor' => $totalAmountMinor],
            ],
            appliedRules: $appliedRules,
            promoCode: $promoCode,
        );
    }

    /** @param array<string, mixed> $context */
    private function matchesRule(MarkupRule $rule, array $context, string $currency): bool
    {
        if ($rule->currency && strtoupper($rule->currency) !== strtoupper($currency)) {
            return false;
        }

        $matchRules = $rule->match_rules ?? [];
        if ($matchRules === []) {
            return true;
        }

        foreach ($matchRules as $field => $expected) {
            $actual = Arr::get($context, (string) $field);

            if (is_array($expected)) {
                if (! in_array($actual, $expected, true)) {
                    return false;
                }
                continue;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /** @param array<string, mixed> $context */
    private function calculateRuleAmount(MarkupRule $rule, int $baseAmountMinor, array $context): int
    {
        return match ($rule->calculation_type) {
            'fixed' => (int) round((float) $rule->value * 100),
            'percentage' => (int) round($baseAmountMinor * ((float) $rule->value / 100)),
            'per_passenger_fixed' => (int) round((float) $rule->value * 100) * max(1, (int) ($context['adult_count'] ?? 1) + (int) ($context['child_count'] ?? 0) + (int) ($context['infant_count'] ?? 0)),
            default => 0,
        };
    }

    private function calculatePromoDiscount(PromoCode $promoCode, int $subtotalAfterMarkup, string $currency): int
    {
        if (! $promoCode->isUsableForCurrency($currency)) {
            throw new RuntimeException('This promo code is not valid for this currency.');
        }

        $discount = match ($promoCode->discount_type) {
            'fixed' => (int) round((float) $promoCode->value * 100),
            'percentage' => (int) round($subtotalAfterMarkup * (min((float) $promoCode->value, (float) config('pricing.max_percentage_discount', 30)) / 100)),
            default => 0,
        };

        if ($promoCode->max_discount_minor) {
            $discount = min($discount, (int) $promoCode->max_discount_minor);
        }

        return max(0, min($discount, $subtotalAfterMarkup - (int) config('pricing.minimum_total_minor', 100)));
    }
}
