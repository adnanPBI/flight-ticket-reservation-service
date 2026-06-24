<?php

namespace App\Actions\Chat;

use App\Models\ChatConversation;

class MergeGuestConversationAction
{
    public function execute(string $visitorToken, int $userId): int
    {
        return ChatConversation::query()
            ->where('visitor_token', $visitorToken)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}
