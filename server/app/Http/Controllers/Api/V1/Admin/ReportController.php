<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ReportFilterRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::with('requester')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function generate(ReportFilterRequest $request): JsonResponse
    {
        $this->authorize('create', Report::class);

        $validated = $request->validated();
        $user = $request->user();
        $format = $validated['format'] ?? 'pdf';

        $report = Report::create([
            'requested_by' => $user->id,
            'report_type' => $validated['report_type'] ?? 'item_list',
            'filters' => $validated,
            'format' => $format,
            'status' => 'ready',
            'file_path' => 'reports/' . uniqid('rep_') . '.' . $format,
            'ready_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Report generated successfully',
            'data' => new ReportResource($report->load('requester')),
        ], JsonResponse::HTTP_CREATED);
    }
}
