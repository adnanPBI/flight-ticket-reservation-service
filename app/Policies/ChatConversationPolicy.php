<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\ChatConversation;
use App\Models\User;

class ChatConversationPolicy
{
    public function view(User|AdminUser $actor, ChatConversation $conversation): bool
    {
        if ($actor instanceof AdminUser) {
            return $actor->is_active && in_array($actor->role, ['super_admin', 'admin', 'support', 'operations'], true);
        }

        return $conversation->user_id !== null && $conversation->user_id === $actor->id;
    }

    public function reply(User|AdminUser $actor, ChatConversation $conversation): bool
    {
        return $actor instanceof AdminUser
            && $actor->is_active
            && in_array($actor->role, ['super_admin', 'admin', 'support', 'operations'], true);
    }
}
