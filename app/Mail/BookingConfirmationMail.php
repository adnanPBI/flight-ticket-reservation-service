<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Booking $booking) {}

    public function build(): self
    {
        return $this
            ->subject('Booking update: '.$this->booking->booking_reference)
            ->view('emails.booking-confirmation')
            ->with(['booking' => $this->booking]);
    }
}
