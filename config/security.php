<?php

return [
    'force_https' => (bool) env('FORCE_HTTPS', true),

    'trusted_proxy_header' => env('TRUSTED_PROXY_HEADER', 'x_forwarded_proto'),

    'headers' => [
        'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),
        'frame_options' => env('SECURITY_FRAME_OPTIONS', 'DENY'),
        'content_type_options' => 'nosniff',
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=(), payment=(self)'),
        'hsts_enabled' => (bool) env('SECURITY_HSTS_ENABLED', true),
        'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'csp_enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
        'csp' => env('SECURITY_CSP', "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; img-src 'self' data: https:; font-src 'self' data: https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https://js.stripe.com; connect-src 'self' https://api.stripe.com wss: ws:; frame-src https://js.stripe.com https://hooks.stripe.com; form-action 'self';"),
    ],

    'rate_limits' => [
        'flight_search_per_minute' => (int) env('RATE_LIMIT_FLIGHT_SEARCH_PER_MINUTE', 30),
        'checkout_per_minute' => (int) env('RATE_LIMIT_CHECKOUT_PER_MINUTE', 20),
        'payment_per_minute' => (int) env('RATE_LIMIT_PAYMENT_PER_MINUTE', 20),
        'webhook_per_minute' => (int) env('RATE_LIMIT_WEBHOOK_PER_MINUTE', 120),
        'chat_per_minute' => (int) env('RATE_LIMIT_CHAT_PER_MINUTE', 60),
        'manage_booking_per_minute' => (int) env('RATE_LIMIT_MANAGE_BOOKING_PER_MINUTE', 10),
        'admin_sensitive_per_minute' => (int) env('RATE_LIMIT_ADMIN_SENSITIVE_PER_MINUTE', 30),
    ],

    'masking' => [
        'enabled' => (bool) env('SENSITIVE_DATA_MASKING', true),
        'keys' => [
            'password', 'password_confirmation', 'token', 'access_token', 'refresh_token', 'secret', 'client_secret',
            'stripe_secret', 'stripe_webhook_secret', 'duffel_access_token', 'amadeus_client_secret', 'authorization',
            'passport_number', 'passport', 'card', 'card_number', 'cvc', 'cvv', 'provider_order_payload',
            // Passenger PII that otherwise leaks into provider/audit log payloads in plaintext.
            'first_name', 'last_name', 'given_name', 'family_name', 'date_of_birth', 'born_on', 'nationality', 'phone',
        ],
    ],

    'pii' => [
        // Bookings in a terminal state are purged of passenger PII after this many days.
        'retention_days' => (int) env('PII_RETENTION_DAYS', 90),
        'purge_batch_size' => (int) env('PII_PURGE_BATCH_SIZE', 200),
    ],
];
