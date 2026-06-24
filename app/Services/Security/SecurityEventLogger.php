<?php

namespace App\Services\Security;

use App\Models\SecurityEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SecurityEventLogger
{
    public function __construct(private readonly SensitiveDataMasker $masker) {}

    /** @param array<string, mixed> $metadata */
    public function record(string $eventType, string $severity = 'info', ?Model $subject = null, array $metadata = [], ?Request $request = null): SecurityEvent
    {
        $request ??= request();

        return SecurityEvent::query()->create([
            'user_id' => $request?->user()?->id,
            'admin_user_id' => auth('admin')->id(),
            'event_type' => $eventType,
            'severity' => $severity,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $this->masker->maskArray($metadata),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
