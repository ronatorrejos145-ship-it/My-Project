<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'remember_token',
        'two_factor_secret',
        'api_token',
        'token',
        'secret',
    ];

    public static function log(
        string $action,
        string $module = 'system',
        ?Model $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $userId = Auth::id();

        $sanitizedOld = $oldValues ? static::sanitize($oldValues) : null;
        $sanitizedNew = $newValues ? static::sanitize($newValues) : null;

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->getKey() : null,
            'old_values' => $sanitizedOld,
            'new_values' => $sanitizedNew,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function logAuth(string $action, ?int $userId = null, ?array $details = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => 'auth',
            'entity_type' => 'App\Models\User',
            'entity_id' => $userId ?? Auth::id(),
            'old_values' => null,
            'new_values' => $details ? static::sanitize($details) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), static::$sensitiveFields)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = static::sanitize($value);
            }
        }

        return $data;
    }
}
