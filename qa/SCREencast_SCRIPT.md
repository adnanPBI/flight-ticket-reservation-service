# Screencast Walkthrough Script

Use this as the final client verification recording outline.

## 1. Environment overview

Show:

```txt
Project URL
Admin URL
Mock/sandbox payment mode
Mock/sandbox flight mode
Horizon/Reverb status if available
```

Say:

```txt
This walkthrough demonstrates the booking flow in safe mock/sandbox mode. Real charging and real airline ticketing remain disabled unless production credentials and approval are configured.
```

## 2. Flight search

Steps:

```txt
Open /flights/search
Select origin and destination
Choose date and passenger count
Submit search
Show results page
Show filters/sorting if available
```

## 3. Fare selection

Steps:

```txt
Open fare details
Show price, baggage, refundability, segments
Select fare
Explain offer revalidation/expiry guard
```

## 4. Passenger details

Steps:

```txt
Fill passenger and contact fields
Submit
Show checkout summary
```

## 5. Payment

Steps:

```txt
Create PaymentIntent/mock payment
Trigger mock payment success or Stripe test payment
Show payment status update
```

## 6. Booking finalization

Steps:

```txt
Run/observe ConfirmBookingJob
Show confirmation page
Show PNR/ticket/provider order snapshot
```

## 7. Admin verification

Steps:

```txt
Open /admin
Open booking detail
Show passengers, segments, payments, provider logs, events
Show failed booking queue if test case exists
```

## 8. Support chat

Steps:

```txt
Open chat widget
Send customer message
Reply from Filament support inbox
Show live/persistent chat history
```

## 9. Manage booking

Steps:

```txt
Open /manage-booking
Enter booking reference and email
Show booking status/timeline/receipt
```

## 10. Close

Say:

```txt
This completes the acceptance path: search, fare selection, passenger details, payment, booking finalization, admin verification, manage booking, and support chat.
```
