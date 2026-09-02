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

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $actorId = $request->input('actor_id') ?? $request->input('user_id');

        $logs = AuditLog::with('actor')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')->toString()))
            ->when($actorId !== null, fn ($q) => $q->where('actor_id', (int) $actorId))
            ->when($request->filled('actor_role'), fn ($q) => $q->where('actor_role', $request->string('actor_role')->toString()))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->string('date_to')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('action', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('actor', fn ($aq) => $aq->where('full_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => AuditLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
                'from'         => $logs->firstItem(),
                'to'           => $logs->lastItem(),
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
