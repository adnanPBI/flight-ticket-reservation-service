<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSegment extends Model
{
    protected $fillable = [
        'booking_id', 'segment_order', 'airline_code', 'flight_number', 'aircraft', 'origin', 'destination',
        'departure_at', 'arrival_at', 'duration_minutes', 'booking_class', 'cabin_class', 'status'
    ];
    protected function casts(): array { return ['departure_at' => 'datetime', 'arrival_at' => 'datetime']; }
    public function booking() { return $this->belongsTo(Booking::class); }
}
