<?php

namespace App\Support\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): AuditLog {
        $actor = $user ?? auth()->user();
        $actorRole = null;
        if ($actor) {
            $actorRole = $actor->getRoleName();
        }

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_role' => $actorRole,
            'action' => $action,
            'auditable_type' => $model ? get_class($model) : ($action),
            'auditable_id' => $model?->getKey() ?? 0,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_id' => session()->getId(),
        ]);
    }
}
