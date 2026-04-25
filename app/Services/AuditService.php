<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AuditService
 *
 * Centralised service for writing audit log entries.
 * Called from controllers and middleware whenever a significant
 * admin or staff action is performed.
 *
 * Usage:
 *   app(AuditService::class)->log($request, 'appointment.created', 'human_appointments', $id, 'New appointment booked.');
 *   AuditService::record($request, 'user.login', 'users', $userId);
 *
 * Owner: Ayman (feature/auth-laravel)
 */
class AuditService
{
    /**
     * Write an audit log entry.
     *
     * @param  Request      $request
     * @param  string       $actionType          e.g. 'user.login', 'appointment.cancelled'
     * @param  string|null  $affectedTable       Database table name
     * @param  int|null     $affectedRecordId    Primary key of affected record
     * @param  string|null  $description         Human-readable description
     * @return AuditLog|null
     */
    public function log(
        Request $request,
        string $actionType,
        ?string $affectedTable = null,
        ?int $affectedRecordId = null,
        ?string $description = null
    ): ?AuditLog {
        try {
            $user = Auth::user();

            return AuditLog::create([
                'action_type'        => $actionType,
                'performed_by'       => $user?->id ?? 0,
                'performed_by_role'  => $user?->role ?? 'system',
                'affected_table'     => $affectedTable,
                'affected_record_id' => $affectedRecordId,
                'description'        => $description,
                'ip_address'         => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Audit log failure must never break the main request
            Log::error('[AuditService] Failed to write audit log', [
                'action'    => $actionType,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Static convenience method.
     * Resolves from the service container automatically.
     *
     * @param  Request      $request
     * @param  string       $actionType
     * @param  string|null  $affectedTable
     * @param  int|null     $affectedRecordId
     * @param  string|null  $description
     * @return AuditLog|null
     */
    public static function record(
        Request $request,
        string $actionType,
        ?string $affectedTable = null,
        ?int $affectedRecordId = null,
        ?string $description = null
    ): ?AuditLog {
        return app(self::class)->log(
            $request,
            $actionType,
            $affectedTable,
            $affectedRecordId,
            $description
        );
    }
}
