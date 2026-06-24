# End-to-End Test Matrix

| Flow | Mode | Required services | Expected final status |
|---|---|---|---|
| Mock search + mock payment + mock ticketing | `FLIGHT_MOCK_MODE=true`, `STRIPE_MOCK_MODE=true` | MySQL, Redis, Horizon | `ticketed` |
| Duffel sandbox search + mock payment + mock ticketing | Duffel sandbox token, Stripe mock | MySQL, Redis, Horizon | `ticketed` mock provider order |
| Duffel sandbox search + Stripe test + mock ticketing | Duffel sandbox + Stripe test keys | MySQL, Redis, Horizon, webhook forwarding | `payment_succeeded` then `ticketed` |
| Real provider finalization dry run | Real provider disabled | MySQL, Redis, Horizon | Must not create real airline order |
| Failed provider simulation | Provider throws/timeout | MySQL, Redis, Horizon | `booking_failed` + admin queue visible |
| Chat flow | Reverb enabled | MySQL, Redis, Reverb | Stored messages + live update |

## Minimum browser matrix

| Browser/device | Must pass |
|---|---|
| Chrome desktop | Full booking flow |
| Chrome Android | Search/passenger/payment/chat |
| Safari iOS | Search/passenger/payment/chat |
| Firefox desktop | Search/admin basic |

## Performance targets for MVP

| Area | Target |
|---|---:|
| Home first load | < 2.5s on normal broadband |
| Search form interaction | No noticeable lag |
| Search result render after backend response | < 1s |
| Checkout page JS error count | 0 blocking errors |
| Admin booking page | < 3s with normal dataset |

## Operational checks

| Check | Expected |
|---|---|
| Horizon dashboard | Workers running |
| Failed jobs | 0 unresolved critical jobs |
| Reverb | Accepts connections |
| Scheduler | Cleanup jobs scheduled |
| Provider logs | No secrets exposed |
| Audit logs | Admin actions logged |
