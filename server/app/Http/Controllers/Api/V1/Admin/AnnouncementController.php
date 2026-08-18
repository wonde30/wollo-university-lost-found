<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAnnouncementRequest;
use App\Http\Resources\Api\V1\AnnouncementResource;
use App\Models\SystemAnnouncement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        $announcements = SystemAnnouncement::with('createdByUser')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => AnnouncementResource::collection($announcements),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $this->authorize('create', SystemAnnouncement::class);

        $announcement = SystemAnnouncement::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Announcement published successfully',
            'data' => new AnnouncementResource($announcement->load('createdByUser')),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $announcement = SystemAnnouncement::with('createdByUser')->findOrFail($id);

        return response()->json([
            'data' => new AnnouncementResource($announcement),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $announcement = SystemAnnouncement::findOrFail($id);
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
