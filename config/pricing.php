<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pricing Safety Controls
    |--------------------------------------------------------------------------
    |
    | The provider fare remains the source of truth. Markup and promo logic only
    | modify the customer-facing total stored on the booking snapshot.
    |
    */
    'minimum_total_minor' => (int) env('PRICING_MINIMUM_TOTAL_MINOR', 100),
    'allow_negative_total' => false,
    'default_currency' => env('PRICING_DEFAULT_CURRENCY', 'USD'),
    'max_percentage_discount' => (float) env('PRICING_MAX_PERCENTAGE_DISCOUNT', 30),
];
