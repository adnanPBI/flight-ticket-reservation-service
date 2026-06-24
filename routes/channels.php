<?php

use App\Actions\Booking\AuthorizeManageBookingAccessAction;
use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Services\Chat\VisitorTokenService;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('booking.{bookingId}', function ($user, int $bookingId) {
    $booking = Booking::find($bookingId);

    if (! $booking) {
        return false;
    }

    $admin = auth('admin')->user();

    if ($admin instanceof AdminUser && Gate::forUser($admin)->allows('view', $booking)) {
        return true;
    }

    if ($user instanceof \App\Models\User && $booking->user_id !== null && $booking->user_id === $user->id) {
        return true;
    }

    if (app(AuthorizeManageBookingAccessAction::class)->allows(request(), $booking)) {
        return true;
    }

    return false;
});

Broadcast::channel('support.conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = ChatConversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    $admin = auth('admin')->user();

    if ($admin instanceof AdminUser && Gate::forUser($admin)->allows('view', $conversation)) {
        return true;
    }

    if ($user instanceof \App\Models\User && $conversation->user_id !== null && $conversation->user_id === $user->id) {
        return true;
    }

    if ($conversation->visitor_token !== null) {
        $cookie = request()->cookie(VisitorTokenService::COOKIE_NAME);

        if ($cookie !== null && hash_equals($conversation->visitor_token, (string) $cookie)) {
            return true;
        }
    }

    return false;
});
