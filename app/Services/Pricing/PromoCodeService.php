<?php

namespace App\Services\Pricing;

use App\Models\PromoCode;
use RuntimeException;

class PromoCodeService
{
    public function findUsable(string $code, string $currency): PromoCode
    {
        $promoCode = PromoCode::query()
            ->whereRaw('LOWER(code) = ?', [strtolower(trim($code))])
            ->first();

        if (! $promoCode) {
            throw new RuntimeException('Promo code was not found.');
        }

        if (! $promoCode->isCurrentlyActive()) {
            throw new RuntimeException('Promo code is not active.');
        }

        if (! $promoCode->isUsableForCurrency($currency)) {
            throw new RuntimeException('Promo code is not valid for this booking currency.');
        }

        return $promoCode;
    }
}
