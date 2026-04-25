<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AdminApiController
 *
 * Exposes admin-only REST endpoints consumed by the AJAX admin dashboard.
 * All routes protected by auth:sanctum + role:admin middleware.
 *
 * Owner: Ayman (feature/auth-laravel) — statistics & audit-log endpoints
 * Full admin CRUD is owned by Amirah (M4).
 */
class AdminApiController extends Controller
{
    /**
     * GET /api/admin/statistics
     * Return system-wide statistics as JSON for live AJAX dashboard counters.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_patients'       => User::where('role', 'patient')->count(),
            'total_pet_owners'     => User::where('role', 'pet_owner')->count(),
            'total_doctors'        => User::where('role', 'doctor')->count(),
            'total_vets'           => User::where('role', 'vet')->count(),
            'total_nurses'         => User::where('role', 'nurse')->count(),
            'total_receptionists'  => User::where('role', 'receptionist')->count(),
            'total_staff'          => User::whereIn('role', ['doctor', 'vet', 'nurse', 'receptionist'])->count(),
            'total_users'          => User::count(),
            'recent_audit_entries' => AuditLog::latest()->take(5)->with('performer')->get()->map(fn ($log) => [
                'id'          => $log->id,
                'action'      => $log->action_type,
                'by'          => $log->performer?->name ?? 'System',
                'role'        => $log->performed_by_role,
                'description' => $log->description,
                'timestamp'   => $log->created_at?->diffForHumans(),
            ]),
        ];

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /**
     * GET /api/admin/audit-logs
     * Return paginated audit log entries for the admin panel.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $logs = AuditLog::with('performer')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $logs->map(fn ($log) => [
                'id'                 => $log->id,
                'action_type'        => $log->action_type,
                'performed_by'       => $log->performer?->name ?? 'Unknown',
                'performed_by_role'  => $log->performed_by_role,
                'affected_table'     => $log->affected_table,
                'affected_record_id' => $log->affected_record_id,
                'description'        => $log->description,
                'ip_address'         => $log->ip_address,
                'created_at'         => $log->created_at?->toISOString(),
                'created_at_human'   => $log->created_at?->diffForHumans(),
            ]),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}
