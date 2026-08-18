<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('actor');

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($actorId = $request->query('actor_id') ?? $request->query('user_id')) {
            $query->where('actor_id', $actorId);
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => AuditLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::with('actor')->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Actor ID', 'Actor Name', 'Actor Role', 'Action', 'Target Type', 'Target ID', 'IP Address', 'Timestamp']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->actor_id,
                    $log->actor?->full_name ?? 'System',
                    $log->actor_role ?? 'N/A',
                    $log->action,
                    $log->auditable_type,
                    $log->auditable_id,
                    $log->ip_address,
                    $log->created_at?->toISOString(),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
