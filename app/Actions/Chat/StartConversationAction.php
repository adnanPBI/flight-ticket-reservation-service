<?php

namespace App\Actions\Chat;

use App\Models\Booking;
use App\Models\ChatConversation;

class StartConversationAction
{
    public function execute(string $visitorToken, ?int $userId = null, ?Booking $booking = null): ChatConversation
    {
        $query = ChatConversation::query()->where('status', 'open');

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('visitor_token', $visitorToken);
        }

        if ($booking) {
            $query->where('booking_id', $booking->id);
        } else {
            $query->whereNull('booking_id');
        }

        $conversation = $query->latest('last_message_at')->first();

        if ($conversation) {
            return $conversation->load('messages');
        }

        return ChatConversation::query()->create([
            'user_id' => $userId,
            'booking_id' => $booking?->id,
            'visitor_token' => $visitorToken,
            'status' => 'open',
            'last_message_at' => now(),
        ])->load('messages');
    }
}
