<?php

namespace App\Actions\Booking;

use App\Models\Booking;

class BuildManageBookingPayloadAction
{
    public function execute(Booking $booking): array
    {
        $booking->loadMissing([
            'passengers',
            'segments' => fn ($query) => $query->orderBy('segment_order'),
            'payments' => fn ($query) => $query->latest('id'),
            'priceBreakdowns',
            'events' => fn ($query) => $query->latest('id')->limit(20),
        ]);

        return [
            'reference' => $booking->booking_reference,
            'status' => $booking->status->value,
            'provider' => $booking->provider->value,
            'provider_order_id' => $booking->provider_order_id,
            'provider_order_status' => $booking->provider_order_status,
            'pnr' => $booking->pnr,
            'ticket_number' => $booking->ticket_number,
            'currency' => $booking->currency,
            'provider_base_amount_minor' => $booking->provider_base_amount_minor,
            'tax_amount_minor' => $booking->tax_amount_minor,
            'fee_amount_minor' => $booking->fee_amount_minor,
            'markup_amount_minor' => $booking->markup_amount_minor,
            'discount_amount_minor' => $booking->discount_amount_minor,
            'total_amount_minor' => $booking->total_amount_minor,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'created_at' => optional($booking->created_at)->toDateTimeString(),
            'confirmed_at' => optional($booking->confirmed_at)->toDateTimeString(),
            'ticketed_at' => optional($booking->ticketed_at)->toDateTimeString(),
            'failed_at' => optional($booking->failed_at)->toDateTimeString(),
            'failure_reason' => $booking->failure_reason,
            'manage_receipt_url' => route('manage-booking.receipt', ['booking' => $booking->booking_reference]),
            'segments' => $booking->segments->map(fn ($segment) => [
                'airline_code' => $segment->airline_code,
                'flight_number' => $segment->flight_number,
                'origin' => $segment->origin,
                'destination' => $segment->destination,
                'departure_at' => optional($segment->departure_at)->toDateTimeString(),
                'arrival_at' => optional($segment->arrival_at)->toDateTimeString(),
                'duration_minutes' => $segment->duration_minutes,
                'cabin_class' => $segment->cabin_class,
                'booking_class' => $segment->booking_class,
            ])->values(),
            'passengers' => $booking->passengers->map(fn ($passenger) => [
                'passenger_type' => $passenger->passenger_type,
                'title' => $passenger->title,
                'first_name' => $passenger->first_name,
                'last_name' => $passenger->last_name,
                'date_of_birth' => optional($passenger->date_of_birth)->toDateString(),
                'nationality' => $passenger->nationality,
            ])->values(),
            'payments' => $booking->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'provider_payment_id' => $payment->provider_payment_id,
                'status' => $payment->status->value,
                'currency' => $payment->currency,
                'amount_minor' => $payment->amount_minor,
                'refunded_amount_minor' => $payment->refunded_amount_minor,
                'paid_at' => optional($payment->paid_at)->toDateTimeString(),
            ])->values(),
            'price_breakdowns' => $booking->priceBreakdowns->map(fn ($row) => [
                'label' => $row->label,
                'type' => $row->type,
                'currency' => $row->currency,
                'amount_minor' => $row->amount_minor,
            ])->values(),
            'events' => $booking->events->map(fn ($event) => [
                'from_status' => $event->from_status,
                'to_status' => $event->to_status,
                'reason' => $event->reason,
                'created_at' => $event->created_at->toDateTimeString(),
            ])->values(),
        ];
    }
}
