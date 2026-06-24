<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Services\Security\SensitiveDataMasker;

class AuditLogger
{
    public function __construct(private readonly SensitiveDataMasker $masker) {}

    public function record(
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?int $actorUserId = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_user_id' => $actorUserId,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'action' => $action,
            'old_values' => $this->masker->maskArray($oldValues),
            'new_values' => $this->masker->maskArray($newValues),
            'metadata' => $this->masker->maskArray($metadata),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
