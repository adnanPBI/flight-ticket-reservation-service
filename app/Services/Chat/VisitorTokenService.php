<?php

namespace App\Services\Chat;

use Illuminate\Support\Str;

class VisitorTokenService
{
    public const COOKIE_NAME = 'ota_chat_visitor_token';

    public function currentOrNew(?string $existing = null): string
    {
        return $existing ?: 'vis_'.Str::random(48);
    }
}
