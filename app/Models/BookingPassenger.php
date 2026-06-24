<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPassenger extends Model
{
    protected $fillable = [
        'booking_id', 'passenger_type', 'title', 'first_name', 'last_name', 'date_of_birth', 'gender', 'nationality',
        'passport_number', 'passport_expiry_date', 'provider_passenger_id'
    ];

    protected $hidden = ['passport_number'];

    protected function casts(): array { return ['date_of_birth' => 'date', 'passport_expiry_date' => 'date']; }
    public function booking() { return $this->belongsTo(Booking::class); }
}
