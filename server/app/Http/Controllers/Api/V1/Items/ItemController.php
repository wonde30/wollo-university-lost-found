<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Items;

use App\Domain\Items\Services\DuplicateDetectionService;
use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Events\ItemReported;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Items\StoreFoundItemRequest;
use App\Http\Requests\Api\V1\Items\StoreLostItemRequest;
use App\Http\Requests\Api\V1\Items\UpdateItemRequest;
use App\Http\Resources\Api\V1\ItemDetailResource;
use App\Http\Resources\Api\V1\ItemResource;
use App\Jobs\SendItemConfirmationEmail;
use App\Models\Campus;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemStatusHistory;
use App\Models\ItemTag;
use App\Models\ItemView;
use App\Models\Location;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Helpers\ReferenceCode;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function __construct(
        protected DuplicateDetectionService $duplicateDetection
    ) {}

    /**
     * Resolve a valid active campus ID from input, location, or authenticated user campus.
     * Throws 422 ValidationException if no valid active campus can be determined.
     */
    protected function resolveCampusId(?int $campusId, ?int $locationId = null, ?User $user = null): int
    {
        if ($campusId && Campus::where('id', $campusId)->where('is_active', true)->exists()) {
            return $campusId;
        }

        if ($locationId) {
            $locationCampusId = Location::where('id', $locationId)->value('campus_id');
            if ($locationCampusId && Campus::where('id', $locationCampusId)->where('is_active', true)->exists()) {
                return (int) $locationCampusId;
            }
        }

        if ($user && isset($user->campus_id) && $user->campus_id) {
            if (Campus::where('id', $user->campus_id)->where('is_active', true)->exists()) {
                return (int) $user->campus_id;
            }
        }

        throw ValidationException::withMessages([
            'campus_id' => ['A valid active campus is required.'],
        ]);
    }

    /**
     * FR-15 / FR-28: Paginated items list with multi-criteria filtering,
     * status filtering, category filtering, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Item::class);

        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $items = Item::with(['category', 'location', 'campus', 'reporter', 'photos', 'tags'])
            ->where('is_deleted', false)
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
            ->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->integer('location_id')))
            ->when($request->filled('reporter_id'), fn ($q) => $q->where('reporter_id', $request->integer('reporter_id')))
            ->when($request->boolean('mine') && auth()->check(), fn ($q) => $q->where('reporter_id', auth()->id()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('reference_code', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('tag'), function ($q) use ($request) {
                $tag = strtolower(trim($request->string('tag')->toString()));
                $q->whereHas('tags', fn ($sub) => $sub->where('tag', $tag));
            })
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('incident_date', '>=', $request->string('from_date')->toString()))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('incident_date', '<=', $request->string('to_date')->toString()))
            ->when($request->query('sort') === 'oldest', fn ($q) => $q->orderBy('created_at', 'asc'))
            ->when($request->query('sort') === 'category_az', fn ($q) => $q->join('categories', 'items.category_id', '=', 'categories.id')->orderBy('categories.name', 'asc')->select('items.*'))
            ->when(! in_array($request->query('sort'), ['oldest', 'category_az'], true), fn ($q) => $q->orderByDesc('created_at'))
            ->paginate($perPage);

        return response()->json([
            'data' => ItemResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'from'         => $items->firstItem(),
                'to'           => $items->lastItem(),
            ],
        ]);
    }

    /**
     * FR-15: Student/Staff reports a LOST item.
     */
    public function storeLost(StoreLostItemRequest $request): JsonResponse
    {
        $this->authorize('create', Item::class);

        $validated = $request->validated();
        $user = $request->user();

        $campusId = $this->resolveCampusId(
            isset($validated['campus_id']) ? (int) $validated['campus_id'] : null,
            isset($validated['location_id']) ? (int) $validated['location_id'] : null,
            $user
        );

        // FR-21: Check for duplicates before creation
        $duplicate = $this->duplicateDetection->check(
            (int) $validated['category_id'],
            $campusId,
            $validated['serial_number'] ?? null
        );

        $item = DB::transaction(function () use ($validated, $user, $campusId, $request) {
            $ref = ReferenceCode::generate('WU');

            $item = Item::create([
                'reference_code' => $ref,
                'reporter_id' => $user->id,
                'campus_id' => $campusId,
                'type' => 'lost',
                'status' => 'lost',
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'location_id' => $validated['location_id'] ?? null,
                'location_detail' => $validated['location_detail'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'color' => $validated['color'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? null,
                'is_high_value' => $validated['is_high_value'] ?? false,
                'last_activity_at' => now(),
            ]);

            // Photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('item-photos', 'public');
                    ItemPhoto::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Tag creation from request
            if (! empty($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    ItemTag::create([
                        'item_id' => $item->id,
                        'tag' => strtolower(trim($tag)),
                    ]);
                }
            }

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => null,
                'to_status' => 'lost',
                'changed_by_role' => $user->getRoleName(),
                'note' => 'Initial lost item report submitted',
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.created', $item, null, $item->toArray(), $user);

            return $item;
        });

        $item->load(['category', 'location', 'campus', 'reporter', 'photos', 'tags']);

        // FR-16: Dispatch confirmation email with reference code
        SendItemConfirmationEmail::dispatch($item);

        // Fire event — listener kicks off async match-suggestion generation
        ItemReported::dispatch($item);

        // In-app notification to reporter
        app(NotificationService::class)->send(new NotificationData(
            userId:  $user->id,
            type:    'item_reported',
            payload: [
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'type'           => 'lost',
                'message'        => "Lost item \"{$item->title}\" (Ref: {$item->reference_code}) has been successfully submitted.",
            ]
        ));

        $response = [
            'message' => 'Lost item report submitted successfully.',
            'data' => new ItemDetailResource($item),
        ];

        if ($duplicate) {
            $response['warning'] = 'A similar item was recently reported. Reference: ' . $duplicate->reference_code;
            $response['similar_item_id'] = $duplicate->id;
        }

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * FR-20: Student/Staff/Security reports a FOUND item.
     */
    public function storeFound(StoreFoundItemRequest $request): JsonResponse
    {
        $this->authorize('create', Item::class);

        $validated = $request->validated();
        $user = $request->user();

        $campusId = $this->resolveCampusId(
            isset($validated['campus_id']) ? (int) $validated['campus_id'] : null,
            isset($validated['location_id']) ? (int) $validated['location_id'] : null,
            $user
        );

        // FR-21: Duplicate detection
        $duplicate = $this->duplicateDetection->check(
            (int) $validated['category_id'],
            $campusId,
            $validated['serial_number'] ?? null
        );

        $item = DB::transaction(function () use ($validated, $user, $campusId, $request) {
            $ref = ReferenceCode::generate('WU');
            $heldAt = $validated['held_at'] ?? 'security_office';

            $item = Item::create([
                'reference_code' => $ref,
                'reporter_id' => $user->id,
                'campus_id' => $campusId,
                'type' => 'found',
                'status' => 'found_unclaimed',
                'held_at' => $heldAt,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'location_id' => $validated['location_id'] ?? null,
                'location_detail' => $validated['location_detail'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'color' => $validated['color'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'last_activity_at' => now(),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('item-photos', 'public');
                    ItemPhoto::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Tag creation from request
            if (! empty($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    ItemTag::create([
                        'item_id' => $item->id,
                        'tag' => strtolower(trim($tag)),
                    ]);
                }
            }

            if (! empty($validated['storage_location_id'])) {
                CustodyEvent::create([
                    'item_id' => $item->id,
                    'actor_id' => $user->id,
                    'storage_location_id' => $validated['storage_location_id'],
                    'event_type' => 'deposited',
                    'condition' => 'good',
                    'notes' => 'Initial deposit into physical storage',
                ]);
            }

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => null,
                'to_status' => 'found_unclaimed',
                'changed_by_role' => $user->getRoleName(),
                'note' => 'Found item registered into system',
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.created', $item, null, $item->toArray(), $user);

            return $item;
        });

        $item->load(['category', 'location', 'reporter', 'photos', 'tags']);

        // FR-16: Dispatch confirmation email with reference code
        SendItemConfirmationEmail::dispatch($item);

        // Fire event — listener kicks off async match-suggestion generation
        ItemReported::dispatch($item);

        // In-app notification to reporter
        app(NotificationService::class)->send(new NotificationData(
            userId:  $user->id,
            type:    'item_reported',
            payload: [
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'type'           => 'found',
                'message'        => "Found item \"{$item->title}\" (Ref: {$item->reference_code}) has been successfully registered in inventory.",
            ]
        ));

        $response = [
            'message' => 'Found item report submitted successfully.',
            'data' => new ItemDetailResource($item),
        ];

        if ($duplicate) {
            $response['warning'] = 'A similar item was recently reported in this area. Reference: ' . $duplicate->reference_code;
            $response['similar_item_id'] = $duplicate->id;
        }

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * FR-66: Record item view on detail access.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $viewer = $request->user();

        $with = ['category', 'location', 'campus', 'reporter', 'photos', 'tags', 'statusHistories', 'custodyEvents'];

        // BUG-02 fix: eager-load approved claim by viewer so ItemDetailResource
        // can check canViewFullContact without firing an extra N+1 query per item.
        if ($viewer) {
            $viewerId = $viewer->id;
            $with['approvedClaimByViewer'] = fn ($q) => $q->where('claimant_id', $viewerId)->where('status', 'approved');
        }

        $item = Item::with($with)
            ->withCount('claims')
            ->findOrFail($id);

        // FR-66: Record view
        ItemView::create([
            'item_id' => $item->id,
            'user_id' => $viewer?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'viewed_at' => now(),
        ]);

        return response()->json([
            'data' => new ItemDetailResource($item),
        ]);
    }

    /**
     * FR-17: Reporter edits own report within 48 hours while status is "lost".
     * FR-22: Finder edits within 24 hours for found items.
     * After that, Staff/Admin intervention required. All edits logged with old/new snapshots.
     */
    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item);

        $user = $request->user();

        // FR-17/FR-22: Time-window enforcement for non-staff users
        if (! $user->isAdmin() && ! $user->isOfficer()) {
            $hoursLimit = $item->type === 'lost'
                ? (int) SystemSetting::get('item_edit_window_lost_hours', 48)
                : (int) SystemSetting::get('item_edit_window_found_hours', 24);
            $hoursSinceCreation = $item->created_at->diffInHours(now());

            if ($hoursSinceCreation > $hoursLimit) {
                return response()->json([
                    'message' => "You can only edit your report within {$hoursLimit} hours of submission. Please contact staff for assistance.",
                ], JsonResponse::HTTP_FORBIDDEN);
            }
        }

        // Capture old values for audit snapshot
        $oldValues = $item->toArray();

        $item->update($request->validated());

        // FR-17/FR-22: All edits written to audit_logs with old/new JSON snapshots
        AuditLogger::log('item.updated', $item, $oldValues, $item->fresh()->toArray(), $user);

        return response()->json([
            'message' => 'Item updated successfully.',
            'data' => new ItemDetailResource($item->load(['category', 'location', 'reporter', 'photos'])),
        ]);
    }

    /**
     * FR-18: Withdraw Lost Report — reporter marks own lost item as "found privately".
     */
    public function withdraw(Request $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);

        $user = $request->user();

        // Only the reporter can withdraw their own item
        if ($item->reporter_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'You can only withdraw your own reports.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        // Can only withdraw items in lost or found_unclaimed status
        if (! in_array((string) $item->status, ['lost', 'found_unclaimed'], true)) {
            return response()->json([
                'message' => 'Only items with status "lost" or "found_unclaimed" can be withdrawn.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fromStatus = (string) $item->status;

        DB::transaction(function () use ($item, $fromStatus, $user, $request) {
            $item->update([
                'status' => 'withdrawn',
                'last_activity_at' => now(),
            ]);

            // FR-18: Withdrawal logged to item_status_histories
            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => 'withdrawn',
                'changed_by_role' => $user->getRoleName(),
                'note' => $request->input('reason', 'Item withdrawn by reporter (found privately)'),
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.withdrawn', $item, ['status' => $fromStatus], ['status' => 'withdrawn'], $user);
        });

        return response()->json([
            'message' => 'Item withdrawn successfully. It has been removed from the active listing.',
            'data' => new ItemResource($item),
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'   => ['required', 'integer'],
            'campus_id'     => ['nullable', 'integer'],
            'serial_number' => ['nullable', 'string', 'max:100'],
        ]);

        $duplicate = $this->duplicateDetection->check(
            (int) $validated['category_id'],
            isset($validated['campus_id']) ? (int) $validated['campus_id'] : null,
            $validated['serial_number'] ?? null
        );

        if ($duplicate) {
            return response()->json([
                'duplicate_found' => true,
                'message'         => 'A similar item report was found recently.',
                'similar_item_id' => $duplicate->id,
            ]);
        }

        return response()->json([
            'duplicate_found' => false,
        ]);
    }

    /**
     * Check if current user has existing reports that might conflict with a cross-link action.
     * Used by frontend to show warnings before "I Found This" or "This Is Mine" actions.
     */
    public function checkCrossLinkEligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'target_item_id' => ['required', 'integer', 'exists:items,id'],
            'action'         => ['required', 'in:report_found,report_lost'],
        ]);

        $targetItem = Item::findOrFail($validated['target_item_id']);

        // Check: Does user already have a report of the opposite type with same category?
        $oppositeType = $validated['action'] === 'report_found' ? 'found' : 'lost';
        $existingReport = Item::where('reporter_id', $user->id)
            ->where('type', $oppositeType)
            ->where('category_id', $targetItem->category_id)
            ->where('is_deleted', false)
            ->whereNotIn('status', ['withdrawn', 'cancelled', 'expired', 'disposed'])
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        // Check: Is user the reporter of the target item? (can't cross-link own item)
        $isOwnItem = (int) $targetItem->reporter_id === (int) $user->id;

        return response()->json([
            'eligible'     => ! $isOwnItem,
            'has_existing' => (bool) $existingReport,
            'existing_ref' => $existingReport?->reference_code,
            'existing_id'  => $existingReport?->id,
            'is_own_item'  => $isOwnItem,
        ]);
    }

    public function destroy(int $id): \Illuminate\Http\Response
    {
        $item = Item::findOrFail($id);
        $this->authorize('delete', $item);

        $item->delete();
        AuditLogger::log('item.deleted', $item, null, null, auth()->user());

        return response()->noContent();
    }
}
