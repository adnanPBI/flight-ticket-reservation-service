@php
    $amount = number_format(($booking->total_amount_minor ?? 0) / 100, 2);
@endphp

<h1>Booking update</h1>

<p>Your booking reference is <strong>{{ $booking->booking_reference }}</strong>.</p>

<p>Status: <strong>{{ $booking->status->label() }}</strong></p>

@if ($booking->pnr)
    <p>PNR: <strong>{{ $booking->pnr }}</strong></p>
@endif

@if ($booking->ticket_number)
    <p>Ticket number: <strong>{{ $booking->ticket_number }}</strong></p>
@endif

<p>Total: <strong>{{ $booking->currency }} {{ $amount }}</strong></p>

<p>Please keep this email for your records. If the status is pending, our support/admin team should verify the booking before sending final travel instructions.</p>
