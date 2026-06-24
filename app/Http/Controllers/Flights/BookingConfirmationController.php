<?php

namespace App\Http\Controllers\Flights;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class BookingConfirmationController extends Controller
{
    public function show(Booking $booking, AuthorizeManageBookingAccessAction $access): Response
    {
        abort_unless($access->allows(request(), $booking), 403, 'You are not authorized to view this booking.');

        $booking->load([
            'payments' => fn ($query) => $query->latest('id'),
            'segments' => fn ($query) => $query->orderBy('segment_order'),
            'passengers',
            'events' => fn ($query) => $query->latest('id')->limit(8),
        ]);

        $payment = $booking->payments->first();

        return Inertia::render('Flights/Confirmation', [
            'booking' => [
                'reference' => $booking->booking_reference,
                'status' => $booking->status->value,
                'provider' => $booking->provider->value,
                'provider_order_id' => $booking->provider_order_id,
                'provider_order_status' => $booking->provider_order_status,
                'pnr' => $booking->pnr,
                'ticket_number' => $booking->ticket_number,
                'currency' => $booking->currency,
                'total_amount_minor' => $booking->total_amount_minor,
                'failure_reason' => $booking->failure_reason,
                'confirmed_at' => optional($booking->confirmed_at)->toDateTimeString(),
                'ticketed_at' => optional($booking->ticketed_at)->toDateTimeString(),
                'phase_note' => 'Phase 7 adds queue-based provider order finalization. Mock mode creates a safe mock Duffel order; real Duffel creation still needs FLIGHT_REAL_ORDER_FINALIZATION=true.',
            ],
            'payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status->value,
                'provider_payment_id' => $payment->provider_payment_id,
                'amount_minor' => $payment->amount_minor,
                'currency' => $payment->currency,
                'mock_mode' => (bool) ($payment->metadata['mock_mode'] ?? false),
            ] : null,
            'events' => $booking->events->map(fn ($event) => [
                'from_status' => $event->from_status,
                'to_status' => $event->to_status,
                'reason' => $event->reason,
                'created_at' => $event->created_at->toDateTimeString(),
            ])->values(),
        ]);
    }
}
