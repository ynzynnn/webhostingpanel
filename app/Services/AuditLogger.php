<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(string $action, string $description, ?int $userId = null, ?array $payload = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'payload' => $payload,
        ]);
    }
}
