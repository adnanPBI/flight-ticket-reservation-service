# UAT Test Plan — OTA Flight Booking

## Test environment

| Item | Value |
|---|---|
| URL | |
| Build ZIP | Phase 15–16 |
| Payment mode | Mock / Stripe test / Live |
| Flight mode | Mock / Duffel sandbox / Live |
| Browser/device | |
| Tester | |

## UAT scenarios

### 1. Public website

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| UI-01 | Open homepage | Page loads without console-breaking errors | ☐ Pass ☐ Fail |
| UI-02 | Open flight search page | Search form is visible on mobile and desktop | ☐ Pass ☐ Fail |
| UI-03 | Use airport autocomplete | Airports can be selected | ☐ Pass ☐ Fail |

### 2. Flight search and fare selection

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| FL-01 | Search one-way flight | Results page shows normalized offers | ☐ Pass ☐ Fail |
| FL-02 | Search round-trip flight | Results page handles outbound/return data | ☐ Pass ☐ Fail |
| FL-03 | Select fare | Booking draft is created | ☐ Pass ☐ Fail |
| FL-04 | Expired offer | User cannot proceed | ☐ Pass ☐ Fail |

### 3. Passenger details

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| PX-01 | Submit valid adult passenger | Booking moves to passenger_details_added | ☐ Pass ☐ Fail |
| PX-02 | Missing passenger fields | Validation errors appear | ☐ Pass ☐ Fail |
| PX-03 | Passenger count mismatch | Submission is blocked | ☐ Pass ☐ Fail |

### 4. Payment

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| PY-01 | Create payment intent | Payment row is created | ☐ Pass ☐ Fail |
| PY-02 | Mock payment success | Booking moves to payment_succeeded | ☐ Pass ☐ Fail |
| PY-03 | Duplicate webhook/event | Event is ignored safely | ☐ Pass ☐ Fail |
| PY-04 | Payment fail/cancel | Booking does not finalize | ☐ Pass ☐ Fail |

### 5. Provider booking finalization

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| BK-01 | Confirm booking job runs | Booking moves to ticketed in mock mode | ☐ Pass ☐ Fail |
| BK-02 | Provider failure | Booking moves to booking_failed and admin sees it | ☐ Pass ☐ Fail |
| BK-03 | Retry failed booking | Retry action dispatches job | ☐ Pass ☐ Fail |

### 6. Admin

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| AD-01 | Admin login | `/admin` loads for active admin | ☐ Pass ☐ Fail |
| AD-02 | Booking detail | Passengers/segments/payments/events are visible | ☐ Pass ☐ Fail |
| AD-03 | Provider logs | Raw/masked logs are visible to admin | ☐ Pass ☐ Fail |
| AD-04 | Failed booking queue | Failed booking appears | ☐ Pass ☐ Fail |
| AD-05 | Markup/promo | Admin can create active pricing rules | ☐ Pass ☐ Fail |

### 7. Chat

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| CH-01 | Guest starts chat | Conversation and visitor token created | ☐ Pass ☐ Fail |
| CH-02 | User sends message | Message saved and broadcast event fired | ☐ Pass ☐ Fail |
| CH-03 | Admin replies | Reply appears to customer | ☐ Pass ☐ Fail |

### 8. Manage booking

| ID | Scenario | Expected result | Result |
|---|---|---|---|
| MB-01 | Lookup by reference/email | Verified session can view booking | ☐ Pass ☐ Fail |
| MB-02 | Wrong email | Access denied | ☐ Pass ☐ Fail |
| MB-03 | Receipt page | Receipt is viewable | ☐ Pass ☐ Fail |

## Sign-off

| Role | Name | Signature/date |
|---|---|---|
| Client reviewer | | |
| Technical reviewer | | |
