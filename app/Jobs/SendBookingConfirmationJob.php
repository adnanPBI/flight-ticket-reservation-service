<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::query()->with(['passengers', 'segments', 'payments'])->findOrFail($this->bookingId);

        if (! $booking->customer_email) {
            Log::warning('Booking confirmation email skipped because customer_email is missing.', ['booking_id' => $booking->id]);
            return;
        }

        Mail::to($booking->customer_email)->send(new BookingConfirmationMail($booking));
    }
}
