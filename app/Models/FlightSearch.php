<?php

namespace App\Models;

use App\Enums\FlightProvider;
use Illuminate\Database\Eloquent\Model;

class FlightSearch extends Model
{
    protected $fillable = [
        'user_id', 'search_reference', 'provider', 'origin', 'destination', 'departure_date', 'return_date',
        'trip_type', 'adult_count', 'child_count', 'infant_count', 'cabin_class', 'currency', 'expires_at',
        'raw_request', 'raw_response_summary'
    ];

    protected function casts(): array
    {
        return [
            'provider' => FlightProvider::class,
            'departure_date' => 'date',
            'return_date' => 'date',
            'expires_at' => 'datetime',
            'raw_request' => 'array',
            'raw_response_summary' => 'array',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function offers() { return $this->hasMany(FlightOffer::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
}
