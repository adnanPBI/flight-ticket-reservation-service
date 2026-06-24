<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\FlightProvider;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'flight_search_id', 'flight_offer_id', 'booking_reference', 'status', 'provider', 'provider_offer_id',
        'provider_order_id', 'provider_order_status', 'provider_order_payload', 'pnr', 'ticket_number', 'currency', 'provider_base_amount_minor', 'tax_amount_minor',
        'fee_amount_minor', 'markup_amount_minor', 'discount_amount_minor', 'applied_promo_code_id', 'applied_promo_code',
        'pricing_snapshot', 'pricing_locked_at', 'total_amount_minor', 'offer_expires_at',
        'confirmed_at', 'ticketed_at', 'ticketing_last_checked_at', 'failed_at', 'customer_last_viewed_at', 'last_manage_lookup_at', 'manage_lookup_count', 'failure_reason', 'customer_email', 'customer_phone', 'pii_redacted_at'
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'provider' => FlightProvider::class,
            'provider_order_payload' => 'array',
            'pricing_snapshot' => 'array',
            'pricing_locked_at' => 'datetime',
            'offer_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'ticketed_at' => 'datetime',
            'ticketing_last_checked_at' => 'datetime',
            'failed_at' => 'datetime',
            'customer_last_viewed_at' => 'datetime',
            'last_manage_lookup_at' => 'datetime',
            'pii_redacted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Booking $booking): void {
            // Log tables survive the DB cascade (SET NULL / polymorphic), so their
            // captured PII is redacted before the booking row is removed.
            app(\App\Actions\Booking\RedactBookingPiiAction::class)->redactAssociatedLogs($booking);
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function search() { return $this->belongsTo(FlightSearch::class, 'flight_search_id'); }
    public function offer() { return $this->belongsTo(FlightOffer::class, 'flight_offer_id'); }
    public function promoCode() { return $this->belongsTo(PromoCode::class, 'applied_promo_code_id'); }
    public function passengers() { return $this->hasMany(BookingPassenger::class); }
    public function segments() { return $this->hasMany(BookingSegment::class); }
    public function priceBreakdowns() { return $this->hasMany(BookingPriceBreakdown::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function events() { return $this->hasMany(BookingEvent::class); }
}
