<?php

namespace App\Http\Controllers\Support;

use App\Actions\Chat\StartConversationAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Chat\VisitorTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StartConversationController extends Controller
{
    public function __invoke(Request $request, StartConversationAction $action, VisitorTokenService $visitorTokens): JsonResponse
    {
        $visitorToken = $visitorTokens->currentOrNew($request->cookie(VisitorTokenService::COOKIE_NAME));
        $booking = null;

        if ($request->filled('booking_reference')) {
            $booking = Booking::query()->where('booking_reference', $request->string('booking_reference')->toString())->first();
        }

        $conversation = $action->execute(
            visitorToken: $visitorToken,
            userId: $request->user()?->id,
            booking: $booking,
        );

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'booking_reference' => $conversation->booking?->booking_reference,
                'messages' => $conversation->messages()->oldest()->get()->map(fn ($message) => [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'body' => $message->body,
                    'created_at' => optional($message->created_at)->toIso8601String(),
                ])->values(),
            ],
            'visitor_token' => $visitorToken,
            'broadcast_channel' => 'support.conversation.'.$conversation->id,
        ])->withCookie(cookie(
            name: VisitorTokenService::COOKIE_NAME,
            value: $visitorToken,
            minutes: 60 * 24 * 180,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'Lax',
        ));
    }
}
