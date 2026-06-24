<?php

namespace App\Http\Controllers\Support;

use App\Actions\Chat\SendChatMessageAction;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\Chat\VisitorTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendMessageController extends Controller
{
    public function __invoke(Request $request, ChatConversation $conversation, SendChatMessageAction $action): JsonResponse
    {
        abort_unless($this->canAccess($request, $conversation), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $message = $action->execute(
            conversation: $conversation,
            body: $validated['body'],
            senderType: 'customer',
            senderId: $request->user()?->id,
            metadata: ['source' => 'public_chat_widget'],
        );

        return response()->json([
            'message' => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'created_at' => optional($message->created_at)->toIso8601String(),
            ],
        ], 201);
    }

    private function canAccess(Request $request, ChatConversation $conversation): bool
    {
        if ($request->user() && $conversation->user_id === $request->user()->id) {
            return true;
        }

        return $conversation->visitor_token && $conversation->visitor_token === $request->cookie(VisitorTokenService::COOKIE_NAME);
    }
}
