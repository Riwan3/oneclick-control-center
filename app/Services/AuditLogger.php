<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(
        string $action,
        string $description,
        array $metadata = [],
        ?string $entityType = null,
        ?int $entityId = null,
        ?int $userId = null
    ): void {
        $resolvedUserId = $userId ?? Auth::id();

        AuditLog::create([
            'user_id' => $resolvedUserId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => empty($metadata) ? null : $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

