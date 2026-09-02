<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAnnouncementRequest;
use App\Http\Resources\Api\V1\AnnouncementResource;
use App\Models\SystemAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SystemAnnouncement::class);

        $query = SystemAnnouncement::with('createdByUser')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('audience'), fn ($q) => $q->where('audience', $request->string('audience')->toString()))
            ->when($request->filled('target_role'), fn ($q) => $q->where('audience', $request->string('target_role')->toString()))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at');

        if ($request->boolean('all')) {
            return response()->json([
                'data' => AnnouncementResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $announcements = $query->paginate($perPage);

        return response()->json([
            'data' => AnnouncementResource::collection($announcements),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page'    => $announcements->lastPage(),
                'per_page'     => $announcements->perPage(),
                'total'        => $announcements->total(),
                'from'         => $announcements->firstItem(),
                'to'           => $announcements->lastItem(),
            ],
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
        $this->authorize('view', $announcement);

        return response()->json([
            'data' => new AnnouncementResource($announcement),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, int $id): JsonResponse
    {
        $announcement = SystemAnnouncement::findOrFail($id);
        $this->authorize('update', $announcement);

        $announcement->update($request->validated());

        return response()->json([
            'message' => 'Announcement updated successfully',
            'data' => new AnnouncementResource($announcement->fresh()->load('createdByUser')),
        ]);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $announcement = SystemAnnouncement::findOrFail($id);
        $this->authorize('update', $announcement);

        $announcement->is_active = ! $announcement->is_active;
        $announcement->save();

        return response()->json([
            'message' => $announcement->is_active ? 'Announcement activated' : 'Announcement deactivated',
            'data' => new AnnouncementResource($announcement->fresh()->load('createdByUser')),
        ]);
    }

    public function bulkToggle(Request $request): JsonResponse
    {
        $this->authorize('create', SystemAnnouncement::class);

        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:system_announcements,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        SystemAnnouncement::whereIn('id', $request->input('ids'))
            ->update(['is_active' => $request->boolean('is_active')]);

        return response()->json([
            'message' => 'Bulk status updated successfully',
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('create', SystemAnnouncement::class);

        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:system_announcements,id'],
        ]);

        SystemAnnouncement::whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'message' => 'Selected announcements deleted successfully',
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
