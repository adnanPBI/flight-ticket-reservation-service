<?php

namespace App\Http\Controllers\Flights;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Actions\Booking\StorePassengerDetailsAction;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePassengerDetailsRequest;
use App\Models\Booking;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PassengerDetailsController extends Controller
{
    public function edit(Booking $booking, AuthorizeManageBookingAccessAction $access): Response
    {
        abort_unless($access->allows(request(), $booking), 403, 'You are not authorized to view this booking.');

        $booking->load(['offer', 'search', 'passengers', 'segments', 'priceBreakdowns']);

        abort_if(
            ! in_array($booking->status, [BookingStatus::OfferSelected, BookingStatus::PassengerDetailsAdded], true),
            409,
            'Passenger details cannot be edited for this booking status.'
        );

        return Inertia::render('Flights/PassengerDetails', [
            'booking' => $this->bookingPayload($booking),
        ]);
    }

    public function store(StorePassengerDetailsRequest $request, Booking $booking, StorePassengerDetailsAction $action, AuthorizeManageBookingAccessAction $access): RedirectResponse
    {
        abort_unless($access->allows($request, $booking), 403, 'You are not authorized to modify this booking.');

        try {
            $booking = $action->execute($booking, $request->validated(), $request->user()?->id);
        } catch (DomainException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return redirect()
            ->route('flights.checkout', $booking->booking_reference)
            ->with('success', 'Passenger details saved. Checkout is ready for Phase 6 Stripe wiring.');
    }

    /** @return array<string, mixed> */
    private function bookingPayload(Booking $booking): array
    {
        return [
            'reference' => $booking->booking_reference,
            'status' => $booking->status->value,
            'currency' => $booking->currency,
            'total_amount_minor' => $booking->total_amount_minor,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'offer_expires_at' => optional($booking->offer_expires_at)->toIso8601String(),
            'passenger_counts' => [
                'adult' => $booking->search?->adult_count ?? 1,
                'child' => $booking->search?->child_count ?? 0,
                'infant_without_seat' => $booking->search?->infant_count ?? 0,
            ],
            'passenger_count' => $booking->search
                ? $booking->search->adult_count + $booking->search->child_count + $booking->search->infant_count
                : 1,
            'passengers' => $booking->passengers->map(fn ($passenger) => [
                'passenger_type' => $passenger->passenger_type,
                'title' => $passenger->title,
                'first_name' => $passenger->first_name,
                'last_name' => $passenger->last_name,
                'date_of_birth' => optional($passenger->date_of_birth)->toDateString(),
                'gender' => $passenger->gender,
                'nationality' => $passenger->nationality,
                'passport_number' => null,
                'passport_expiry_date' => optional($passenger->passport_expiry_date)->toDateString(),
            ])->values(),
            'offer' => $booking->offer ? [
                'airline_name' => $booking->offer->airline_name,
                'airline_code' => $booking->offer->airline_code,
                'origin' => $booking->offer->origin,
                'destination' => $booking->offer->destination,
                'departure_at' => optional($booking->offer->departure_at)->toIso8601String(),
                'arrival_at' => optional($booking->offer->arrival_at)->toIso8601String(),
                'baggage_summary' => $booking->offer->baggage_summary,
                'refundability' => $booking->offer->refundability,
            ] : null,
            'segments' => $booking->segments->map(fn ($segment) => [
                'airline_code' => $segment->airline_code,
                'flight_number' => $segment->flight_number,
                'origin' => $segment->origin,
                'destination' => $segment->destination,
                'departure_at' => optional($segment->departure_at)->toIso8601String(),
                'arrival_at' => optional($segment->arrival_at)->toIso8601String(),
                'duration_minutes' => $segment->duration_minutes,
                'cabin_class' => $segment->cabin_class,
            ])->values(),
            'price_breakdown' => $booking->priceBreakdowns->map(fn ($item) => [
                'label' => $item->label,
                'type' => $item->type,
                'currency' => $item->currency,
                'amount_minor' => $item->amount_minor,
            ])->values(),
        ];
    }
}
