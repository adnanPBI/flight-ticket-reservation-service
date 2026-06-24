<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\FlightProviderLog;
use Illuminate\Support\Facades\DB;

class RedactBookingPiiAction
{
    /** @var array<string, mixed> */
    private const REDACTED_PASSENGER_FIELDS = [
        'first_name' => '[redacted]',
        'last_name' => '[redacted]',
        'title' => null,
        'gender' => null,
        'nationality' => null,
        'passport_number' => null,
        'passport_expiry_date' => null,
        'date_of_birth' => null,
        'provider_passenger_id' => null,
    ];

    public function execute(Booking $booking, bool $force = false): Booking
    {
        if (! $force && $booking->pii_redacted_at !== null) {
            return $booking;
        }

        return DB::transaction(function () use ($booking): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            $booking->passengers()->update(self::REDACTED_PASSENGER_FIELDS);

            $booking->forceFill([
                'customer_email' => null,
                'customer_phone' => null,
                'pii_redacted_at' => now(),
            ])->save();

            $this->redactAssociatedLogs($booking);

            return $booking->refresh();
        });
    }

    /**
     * Redact the PII captured in log tables that survive a booking's cascade
     * deletion (flight_provider_logs uses SET NULL, audit_logs is polymorphic).
     */
    public function redactAssociatedLogs(Booking $booking): void
    {
        FlightProviderLog::query()
            ->where('booking_id', $booking->id)
            ->update([
                'request_payload' => null,
                'response_payload' => null,
            ]);

        DB::table('audit_logs')
            ->where('auditable_type', Booking::class)
            ->where('auditable_id', $booking->id)
            ->update([
                'old_values' => null,
                'new_values' => null,
                'metadata' => null,
            ]);
    }
}
