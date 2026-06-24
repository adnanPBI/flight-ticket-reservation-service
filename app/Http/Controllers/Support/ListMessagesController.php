<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\Chat\VisitorTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMessagesController extends Controller
{
    public function __invoke(Request $request, ChatConversation $conversation): JsonResponse
    {
        abort_unless($this->canAccess($request, $conversation), 403);

        return response()->json([
            'messages' => $conversation->messages()->oldest()->get()->map(fn ($message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'created_at' => optional($message->created_at)->toIso8601String(),
            ])->values(),
        ]);
    }

    private function canAccess(Request $request, ChatConversation $conversation): bool
    {
        if ($request->user() && $conversation->user_id === $request->user()->id) {
            return true;
        }

        return $conversation->visitor_token && $conversation->visitor_token === $request->cookie(VisitorTokenService::COOKIE_NAME);
    }
}
