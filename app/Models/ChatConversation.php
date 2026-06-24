<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['user_id', 'booking_id', 'visitor_token', 'status', 'last_message_at'];
    protected function casts(): array { return ['last_message_at' => 'datetime']; }

    public function user() { return $this->belongsTo(User::class); }
    public function booking() { return $this->belongsTo(Booking::class); }
    public function messages() { return $this->hasMany(ChatMessage::class); }

    public function latestMessage() { return $this->hasOne(ChatMessage::class)->latestOfMany(); }
}
