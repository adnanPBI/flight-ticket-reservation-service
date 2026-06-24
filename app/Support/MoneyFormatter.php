<?php

namespace App\Support;

class MoneyFormatter
{
    public static function minor(?int $amountMinor, ?string $currency = 'USD'): string
    {
        $amount = ($amountMinor ?? 0) / 100;

        return sprintf('%s %0.2f', strtoupper($currency ?: 'USD'), $amount);
    }
}
