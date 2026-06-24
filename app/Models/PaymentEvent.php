<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    protected $fillable = ['payment_id', 'from_status', 'to_status', 'actor_user_id', 'reason', 'provider_event_id', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function payment() { return $this->belongsTo(Payment::class); }
}
