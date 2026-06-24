# Phase 13 — Security and audit hardening

This phase adds production-oriented safeguards around routes, headers, audit logs, provider payloads, admin access, and operational security events.

## Added items

- `config/security.php`
- `SecurityHeaders` middleware
- `EnsureProductionHttps` middleware
- Named rate limiters for search, checkout, payment, webhook, chat, manage-booking, and admin-sensitive actions
- Security policies for bookings, payments, chat conversations, and audit logs
- `SensitiveDataMasker` service
- Masked audit logging
- Masked provider request/response logging
- `security_events` table
- `SecurityEventLogger`
- Filament `SecurityEventResource`
- `ota:security-audit-check` command
- `/health/secure` JSON health endpoint

## Important defaults

Local mode is intentionally relaxed:

```env
APP_ENV=local
APP_DEBUG=true
FORCE_HTTPS=false
SECURITY_HSTS_ENABLED=false
STRIPE_MOCK_MODE=true
FLIGHT_MOCK_MODE=true
```

Production should be stricter:

```env
APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true
SECURITY_HSTS_ENABLED=true
SECURITY_HEADERS_ENABLED=true
SECURITY_CSP_ENABLED=true
SENSITIVE_DATA_MASKING=true
```

## Rate limiter names

```txt
flight-search
checkout
payment
payment-webhook
chat
manage-booking
admin-sensitive
```

These names are used in `routes/web.php`.

## Security event log

`security_events` is for suspicious or operational security events that do not belong in normal business audit logs. Examples:

```txt
invalid_webhook_signature
manage_booking_denied
admin_sensitive_action
rate_limit_exceeded_later
unexpected_provider_payload
```

The base logger is ready. Add explicit calls when introducing more sensitive workflows.

## Data masking

The masker masks sensitive keys recursively before saving audit/provider logs. It targets keys like:

```txt
password
token
secret
client_secret
passport_number
card_number
provider_order_payload
```

Do not rely on masking as permission control. It is a last-line log-safety measure.

## Webhook protection

Stripe webhook signature verification remains inside `StripeWebhookController`. Duplicate webhook protection continues through `payment_events.provider_event_id`.

## Production command

Run this before a production go-live:

```bash
php artisan ota:security-audit-check
```

It performs a lightweight readiness check. It is not a replacement for a full security review.

## Remaining hardening for later

- Real admin two-factor authentication
- Device/session management
- Centralized exception reporting
- Full CSP tuning after all third-party scripts are finalized
- Web Application Firewall rules
- Backup restore testing
- Penetration testing before live payment/provider production
