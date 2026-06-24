<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'provider', 'provider_payment_id', 'provider_customer_id', 'status', 'currency', 'amount_minor',
        'refunded_amount_minor', 'client_secret_last4', 'metadata', 'paid_at', 'failed_at'
    ];

    protected function casts(): array
    {
        return ['status' => PaymentStatus::class, 'metadata' => 'array', 'paid_at' => 'datetime', 'failed_at' => 'datetime'];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function events() { return $this->hasMany(PaymentEvent::class); }
}
