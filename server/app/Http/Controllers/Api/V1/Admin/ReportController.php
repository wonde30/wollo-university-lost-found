<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ReportFilterRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Jobs\GenerateReport;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $reports = Report::with('requester')
            ->when($request->filled('report_type'), fn ($q) => $q->where('report_type', $request->string('report_type')->toString()))
            ->when($request->filled('format'), fn ($q) => $q->where('format', $request->string('format')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('report_type', 'like', "%{$search}%")
                        ->orWhere('file_path', 'like', "%{$search}%")
                        ->orWhereHas('requester', fn ($uq) => $uq->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'per_page'     => $reports->perPage(),
                'total'        => $reports->total(),
                'from'         => $reports->firstItem(),
                'to'           => $reports->lastItem(),
            ],
        ]);
    }

    public function generate(ReportFilterRequest $request): JsonResponse
    {
        $this->authorize('create', Report::class);

        $validated = $request->validated();
        $user = $request->user();
        $format = $validated['format'] ?? 'csv';

        // FR-58: Queue generation, status = queued
        $report = Report::create([
            'requested_by' => $user->id,
            'report_type' => $validated['report_type'] ?? 'items',
            'filters' => $validated,
            'format' => $format,
            'status' => 'queued',
        ]);

        GenerateReport::dispatch($report);

        return response()->json([
            'message' => 'Report generation started. It will be available for download shortly.',
            'data' => new ReportResource($report->load('requester')),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * FR-58: Download report (CSV or PDF). Expires after 7 days.
     */
    public function download(Request $request, int $id)
    {
        $report = Report::findOrFail($id);
        
        $this->authorize('view', $report);

        if ($report->status !== 'ready') {
            return response()->json([
                'message' => 'Report is not ready for download yet.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (now()->isAfter($report->expires_at)) {
            return response()->json([
                'message' => 'This report has expired and is no longer available.',
            ], JsonResponse::HTTP_GONE);
        }

        if (!Storage::disk('local')->exists($report->file_path)) {
            return response()->json([
                'message' => 'Report file not found.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return Storage::disk('local')->download(
            $report->file_path, 
            "report_{$report->report_type}_{$report->id}.{$report->format}"
        );
    }
}

