<?php

namespace App\Traits;

use App\Models\User;
use App\Models\AuditLog;

trait Loggable
{
    public function logActivity(string $event, ?User $user = null): void
    {
        AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'event' => $event,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}

