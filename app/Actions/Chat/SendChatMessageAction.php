<?php

namespace App\Actions\Chat;

use App\Events\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SendChatMessageAction
{
    public function execute(ChatConversation $conversation, string $body, string $senderType = 'customer', ?int $senderId = null, array $metadata = []): ChatMessage
    {
        $body = trim($body);

        if ($body === '') {
            throw new RuntimeException('Message body cannot be empty.');
        }

        return DB::transaction(function () use ($conversation, $body, $senderType, $senderId, $metadata): ChatMessage {
            $message = $conversation->messages()->create([
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'body' => $body,
                'metadata' => $metadata,
            ]);

            $conversation->forceFill([
                'last_message_at' => now(),
                'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
            ])->save();

            broadcast(new ChatMessageCreated($message->load('conversation')))->toOthers();

            return $message->refresh();
        });
    }
}
