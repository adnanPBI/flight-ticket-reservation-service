<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingEvent extends Model
{
    protected $fillable = ['booking_id', 'from_status', 'to_status', 'actor_user_id', 'reason', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function booking() { return $this->belongsTo(Booking::class); }
}
