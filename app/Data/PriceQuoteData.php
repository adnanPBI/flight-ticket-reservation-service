<?php

namespace App\Data;

use App\Models\MarkupRule;
use App\Models\PromoCode;

class PriceQuoteData
{
    /**
     * @param list<array<string, mixed>> $breakdown
     * @param list<array<string, mixed>> $appliedRules
     */
    public function __construct(
        public readonly string $currency,
        public readonly int $providerBaseAmountMinor,
        public readonly int $taxAmountMinor,
        public readonly int $feeAmountMinor,
        public readonly int $markupAmountMinor,
        public readonly int $discountAmountMinor,
        public readonly int $totalAmountMinor,
        public readonly array $breakdown,
        public readonly array $appliedRules = [],
        public readonly ?PromoCode $promoCode = null,
    ) {}

    public function toBookingAttributes(): array
    {
        return [
            'currency' => $this->currency,
            'provider_base_amount_minor' => $this->providerBaseAmountMinor,
            'tax_amount_minor' => $this->taxAmountMinor,
            'fee_amount_minor' => $this->feeAmountMinor,
            'markup_amount_minor' => $this->markupAmountMinor,
            'discount_amount_minor' => $this->discountAmountMinor,
            'total_amount_minor' => $this->totalAmountMinor,
            'applied_promo_code_id' => $this->promoCode?->id,
            'applied_promo_code' => $this->promoCode?->code,
            'pricing_snapshot' => $this->toSnapshot(),
            'pricing_locked_at' => now(),
        ];
    }

    public function toSnapshot(): array
    {
        return [
            'currency' => $this->currency,
            'provider_base_amount_minor' => $this->providerBaseAmountMinor,
            'tax_amount_minor' => $this->taxAmountMinor,
            'fee_amount_minor' => $this->feeAmountMinor,
            'markup_amount_minor' => $this->markupAmountMinor,
            'discount_amount_minor' => $this->discountAmountMinor,
            'total_amount_minor' => $this->totalAmountMinor,
            'applied_rules' => $this->appliedRules,
            'promo_code' => $this->promoCode?->only(['id', 'code', 'discount_type', 'value', 'currency', 'max_discount_minor']),
            'locked_at' => now()->toIso8601String(),
        ];
    }
}
