<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_conversation_id', 'sender_type', 'sender_id', 'body', 'metadata', 'read_at'];
    protected function casts(): array { return ['metadata' => 'array', 'read_at' => 'datetime']; }
    public function conversation() { return $this->belongsTo(ChatConversation::class, 'chat_conversation_id'); }
}
