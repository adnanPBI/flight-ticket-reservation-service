<?php

return [
    'expired_search_limit' => env('CLEANUP_EXPIRED_SEARCH_LIMIT', 500),
    'raw_offer_payload_retention_days' => env('CLEANUP_RAW_OFFER_PAYLOAD_RETENTION_DAYS', 14),
    'provider_log_payload_retention_days' => env('CLEANUP_PROVIDER_LOG_PAYLOAD_RETENTION_DAYS', 30),
    'abandoned_payment_minutes' => env('CLEANUP_ABANDONED_PAYMENT_MINUTES', 120),
    'pending_ticketing_alert_minutes' => env('CLEANUP_PENDING_TICKETING_ALERT_MINUTES', 60),
];
