<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

class BookingHistoryController extends Controller
{
    public function __invoke(): Response
    {
        $bookings = Booking::query()
            ->when(auth()->check(), fn ($query) => $query->where('user_id', auth()->id()))
            ->when(! auth()->check(), fn ($query) => $query->whereRaw('1 = 0'))
            ->with(['segments' => fn ($query) => $query->orderBy('segment_order')])
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (Booking $booking) => [
                'reference' => $booking->booking_reference,
                'status' => $booking->status->value,
                'origin' => optional($booking->segments->first())->origin,
                'destination' => optional($booking->segments->last())->destination,
                'departure_at' => optional(optional($booking->segments->first())->departure_at)->toDateTimeString(),
                'currency' => $booking->currency,
                'total_amount_minor' => $booking->total_amount_minor,
                'pnr' => $booking->pnr,
                'manage_url' => route('manage-booking.show', ['booking' => $booking->booking_reference]),
                'created_at' => optional($booking->created_at)->toDateTimeString(),
            ]);

        return Inertia::render('Account/Bookings', [
            'bookings' => $bookings,
            'isAuthenticated' => auth()->check(),
        ]);
    }
}
