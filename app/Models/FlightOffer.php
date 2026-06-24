<?php

namespace App\Models;

use App\Enums\FlightProvider;
use Illuminate\Database\Eloquent\Model;

class FlightOffer extends Model
{
    protected $fillable = [
        'flight_search_id', 'provider', 'provider_offer_id', 'airline_code', 'airline_name', 'origin', 'destination',
        'departure_at', 'arrival_at', 'duration_minutes', 'stops', 'cabin_class', 'fare_brand', 'baggage_summary',
        'refundability', 'currency', 'base_amount_minor', 'tax_amount_minor', 'fee_amount_minor', 'markup_amount_minor',
        'discount_amount_minor', 'total_amount_minor', 'expires_at', 'normalized_payload', 'raw_payload'
    ];

    protected function casts(): array
    {
        return [
            'provider' => FlightProvider::class,
            'departure_at' => 'datetime',
            'arrival_at' => 'datetime',
            'expires_at' => 'datetime',
            'normalized_payload' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function search() { return $this->belongsTo(FlightSearch::class, 'flight_search_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
}
