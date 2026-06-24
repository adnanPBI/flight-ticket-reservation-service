<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Receipt {{ $booking->booking_reference }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 32px; }
        .page { max-width: 860px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; }
        .muted { color: #64748b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .card { border: 1px solid #e2e8f0; border-radius: 18px; padding: 16px; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 10px 6px; text-align: left; font-size: 14px; }
        .total { font-size: 28px; font-weight: 900; }
        @media print { body { background: white; padding: 0; } .page { border: none; } }
    </style>
</head>
<body>
    @php
        $money = fn (int $minor, string $currency) => $currency . ' ' . number_format($minor / 100, 2);
    @endphp

    <main class="page">
        <p class="muted">SkyBridge OTA</p>
        <h1>Booking Receipt</h1>
        <p class="muted">Reference: <strong>{{ $booking->booking_reference }}</strong></p>

        <div class="grid card">
            <div>
                <p class="muted">Booking status</p>
                <strong>{{ $booking->status->value }}</strong>
            </div>
            <div>
                <p class="muted">Payment status</p>
                <strong>{{ optional($booking->payments->first())->status?->value ?? 'not_created' }}</strong>
            </div>
            <div>
                <p class="muted">PNR</p>
                <strong>{{ $booking->pnr ?? 'Pending' }}</strong>
            </div>
            <div>
                <p class="muted">Ticket number</p>
                <strong>{{ $booking->ticket_number ?? 'Pending' }}</strong>
            </div>
        </div>

        <section class="card">
            <h2>Customer</h2>
            <p>{{ $booking->customer_email }}</p>
            <p class="muted">{{ $booking->customer_phone }}</p>
        </section>

        <section class="card">
            <h2>Flight segments</h2>
            <table>
                <thead>
                    <tr><th>Route</th><th>Flight</th><th>Departure</th><th>Arrival</th></tr>
                </thead>
                <tbody>
                    @foreach($booking->segments as $segment)
                        <tr>
                            <td>{{ $segment->origin }} → {{ $segment->destination }}</td>
                            <td>{{ $segment->airline_code }} {{ $segment->flight_number }}</td>
                            <td>{{ optional($segment->departure_at)->format('Y-m-d H:i') ?? 'Pending' }}</td>
                            <td>{{ optional($segment->arrival_at)->format('Y-m-d H:i') ?? 'Pending' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="card">
            <h2>Passengers</h2>
            <table>
                <thead>
                    <tr><th>Name</th><th>Type</th><th>Date of birth</th><th>Nationality</th></tr>
                </thead>
                <tbody>
                    @foreach($booking->passengers as $passenger)
                        <tr>
                            <td>{{ trim($passenger->title . ' ' . $passenger->first_name . ' ' . $passenger->last_name) }}</td>
                            <td>{{ $passenger->passenger_type }}</td>
                            <td>{{ optional($passenger->date_of_birth)->format('Y-m-d') }}</td>
                            <td>{{ $passenger->nationality ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="card">
            <h2>Price breakdown</h2>
            <table>
                <tbody>
                    @forelse($booking->priceBreakdowns as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td style="text-align:right">{{ $money($row->amount_minor, $row->currency) }}</td>
                        </tr>
                    @empty
                        <tr><td>Final total</td><td style="text-align:right">{{ $money($booking->total_amount_minor, $booking->currency) }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p class="total">{{ $money($booking->total_amount_minor, $booking->currency) }}</p>
        </section>
    </main>
</body>
</html>
