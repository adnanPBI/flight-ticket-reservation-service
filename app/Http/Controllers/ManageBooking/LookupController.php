<?php

namespace App\Http\Controllers\ManageBooking;

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LookupController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('ManageBooking/Lookup');
    }

    public function store(Request $request, AuthorizeManageBookingAccessAction $access, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'booking_reference' => ['required', 'string', 'max:50'],
            'customer_email' => ['required', 'email', 'max:255'],
        ]);

        $booking = Booking::query()
            ->where('booking_reference', strtoupper(trim($validated['booking_reference'])))
            ->where('customer_email', strtolower(trim($validated['customer_email'])))
            ->first();

        if (! $booking) {
            return back()->withErrors([
                'booking_reference' => 'No booking matched that reference and email combination.',
            ])->onlyInput('booking_reference', 'customer_email');
        }

        $access->grant($request, $booking);

        $booking->forceFill([
            'last_manage_lookup_at' => now(),
            'manage_lookup_count' => $booking->manage_lookup_count + 1,
        ])->save();

        $auditLogger->record(
            action: 'booking.manage_lookup_granted',
            auditableType: Booking::class,
            auditableId: $booking->id,
            metadata: ['booking_reference' => $booking->booking_reference]
        );

        return redirect()->route('manage-booking.show', ['booking' => $booking->booking_reference]);
    }
}
