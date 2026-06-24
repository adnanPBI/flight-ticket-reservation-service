<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPriceBreakdown extends Model
{
    protected $fillable = ['booking_id', 'label', 'type', 'currency', 'amount_minor', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function booking() { return $this->belongsTo(Booking::class); }
}
