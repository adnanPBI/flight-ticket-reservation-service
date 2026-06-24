<?php

return [
    'mock_mode' => env('STRIPE_MOCK_MODE', true),
    'key' => env('STRIPE_KEY', 'pk_test_mock'),
    'secret' => env('STRIPE_SECRET', 'sk_test_mock'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'currency' => strtolower(env('STRIPE_CURRENCY', 'usd')),

    // Phase 7 can dispatch provider-order finalization after payment success.
    // Real Duffel ticketing is still blocked by FLIGHT_REAL_ORDER_FINALIZATION=false unless explicitly enabled.
    'dispatch_booking_confirmation_job' => env('STRIPE_DISPATCH_CONFIRM_JOB', true),
];
