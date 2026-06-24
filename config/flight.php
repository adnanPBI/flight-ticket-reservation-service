<?php

return [
    'default_provider' => env('FLIGHT_PROVIDER', 'duffel'),

    'mock_mode' => env('FLIGHT_MOCK_MODE', true),

    'duffel' => [
        'base_url' => env('DUFFEL_BASE_URL', 'https://api.duffel.com'),
        'access_token' => env('DUFFEL_ACCESS_TOKEN'),
        'version' => env('DUFFEL_VERSION', 'v2'),
        'timeout' => (int) env('DUFFEL_TIMEOUT', 30),
    ],

    'amadeus' => [
        'base_url' => env('AMADEUS_BASE_URL', 'https://test.api.amadeus.com'),
        'client_id' => env('AMADEUS_CLIENT_ID'),
        'client_secret' => env('AMADEUS_CLIENT_SECRET'),
        'timeout' => (int) env('AMADEUS_TIMEOUT', 30),
    ],

    'search' => [
        'offer_ttl_minutes' => (int) env('FLIGHT_OFFER_TTL_MINUTES', 45),
        'max_results_for_mvp' => (int) env('FLIGHT_MAX_RESULTS_FOR_MVP', 30),
    ],

    'revalidation' => [
        'enabled' => env('FLIGHT_OFFER_REVALIDATION_ENABLED', true),
        'fail_on_expired_offer' => env('FLIGHT_FAIL_ON_EXPIRED_OFFER', true),
    ],

    'orders' => [
        // Hard safety gate: mock orders can still complete in FLIGHT_MOCK_MODE=true.
        // Real Duffel order creation needs this enabled explicitly.
        'real_finalization_enabled' => env('FLIGHT_REAL_ORDER_FINALIZATION', false),
        'auto_refund_failed_bookings' => env('FLIGHT_AUTO_REFUND_FAILED_BOOKINGS', false),
        'ticketing_sync_enabled' => env('FLIGHT_TICKETING_SYNC_ENABLED', true),
    ],
];
