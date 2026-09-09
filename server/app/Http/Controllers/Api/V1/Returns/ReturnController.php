<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Returns;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Events\ItemReturned;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Returns\StoreReturnRequest;
use App\Http\Resources\Api\V1\ReturnResource;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Models\ReturnRecord;
use App\Models\SystemSetting;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records physical handover of found items to verified owners.
 */
class ReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReturnRecord::class);

        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $returns = ReturnRecord::with(['claim.item', 'recipient', 'staff', 'storageLocation'])
            ->when($request->filled('date_from'), fn ($q) => $q->where('return_date', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($q) => $q->where('return_date', '<=', $request->string('date_to')->toString()))
            ->when($request->filled('recipient_id'), fn ($q) => $q->where('returned_to', $request->integer('recipient_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('recipient', fn ($uq) => $uq->where('full_name', 'like', "%{$search}%")->orWhere('university_id', 'like', "%{$search}%"))
                        ->orWhereHas('claim.item', fn ($iq) => $iq->where('title', 'like', "%{$search}%")->orWhere('reference_code', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('return_date')
            ->paginate($perPage);

        return response()->json([
            'data' => ReturnResource::collection($returns),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page'    => $returns->lastPage(),
                'per_page'     => $returns->perPage(),
                'total'        => $returns->total(),
                'from'         => $returns->firstItem(),
                'to'           => $returns->lastItem(),
            ],
        ]);
    }

    public function store(StoreReturnRequest $request): JsonResponse
    {
        $this->authorize('create', ReturnRecord::class);

        $user      = $request->user();
        $validated = $request->validated();

        $return = DB::transaction(function () use ($validated, $user, $request) {

            // Verify the claim is approved before processing a physical return.
            $claim = Claim::lockForUpdate()->findOrFail($validated['claim_id']);

            if ($claim->status !== 'approved') {
                throw ValidationException::withMessages([
                    'claim_id' => [
                        "Only claims with status 'approved' can be processed for a physical return. "
                        . "Current claim status: {$claim->status}.",
                    ],
                ]);
            }

            // Guard: prevent duplicate returns on the same claim
            if ($claim->returnRecord()->exists()) {
                throw ValidationException::withMessages([
                    'claim_id' => ['A return record already exists for this claim.'],
                ]);
            }

            $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

            // Verify the item belongs to this claim
            if ($item->id !== $claim->item_id) {
                throw ValidationException::withMessages([
                    'item_id' => ['The specified item does not match the claim.'],
                ]);
            }

            // Verify item is in claimed state
            if ($item->status !== 'claimed') {
                throw ValidationException::withMessages([
                    'item_id' => [
                        "Item must be in 'claimed' status to process a return. "
                        . "Current status: {$item->status}.",
                    ],
                ]);
            }

            $returnRecord = ReturnRecord::create([
                'claim_id'            => $claim->id,
                'returned_to'         => $validated['returned_to'],
                'handed_over_by'      => $user->id,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'return_date'         => $validated['return_date'],
                'return_time'         => $validated['return_time'] ?? now()->format('H:i:s'),
                'condition_on_return' => $validated['condition_on_return'],
                'notes'               => $validated['notes'] ?? null,
                'confirmation_token'  => \Illuminate\Support\Str::random(48),
                'confirmation_token_expires_at' => now()->addHours(48),
            ]);

            // Transition item to returned
            $fromStatus             = (string) $item->status;
            $item->status           = 'returned';
            $item->last_activity_at = now();
            $item->save();

            ItemStatusHistory::create([
                'item_id'         => $item->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => 'returned',
                'changed_by_role' => $user->getRoleName(),
                'note'            => 'Physical item handed over to verified owner.',
                'ip_address'      => $request->ip(),
            ]);

            // Release from custody
            CustodyEvent::create([
                'item_id'             => $item->id,
                'actor_id'            => $user->id,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'event_type'          => 'released',
                'condition'           => $validated['condition_on_return'],
                'notes'               => 'Released from custody on verified physical return.',
            ]);

            AuditLogger::log('return.recorded', $returnRecord, null, $returnRecord->toArray(), $user);

            return $returnRecord;
        });

        // Fire event — listener notifies claimant of the physical handover
        ItemReturned::dispatch($return);

        // FR-45: Dispatch PDF generation job
        \App\Jobs\GenerateReturnConfirmationPdf::dispatch($return);

        return response()->json([
            'message' => 'Physical return recorded successfully.',
            'data'    => new ReturnResource(
                $return->load(['claim', 'item', 'recipient', 'staff', 'storageLocation'])
            ),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $return = ReturnRecord::with(['claim', 'item', 'recipient', 'staff', 'storageLocation', 'documents'])
            ->findOrFail($id);

        $this->authorize('view', $return);

        return response()->json([
            'data' => new ReturnResource($return),
        ]);
    }

    /**
     * FR-44: Preview return details by secure confirmation token.
     */
    public function getByToken(string $token): JsonResponse
    {
        $returnRecord = ReturnRecord::with(['item', 'claim', 'recipient', 'staff', 'storageLocation'])
            ->where('confirmation_token', $token)
            ->first();

        if (! $returnRecord) {
            return response()->json([
                'message' => 'Invalid or expired confirmation link. If you already confirmed collection, receipt has been registered.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($returnRecord->confirmation_token_expires_at && $returnRecord->confirmation_token_expires_at->isPast()) {
            return response()->json([
                'message' => 'This confirmation link has expired (48h window). Please contact campus security.',
            ], JsonResponse::HTTP_GONE);
        }

        return response()->json([
            'data' => new ReturnResource($returnRecord),
        ]);
    }

    /**
     * FR-44: Recipient confirms physical collection via secure link.
     */
    public function confirmByToken(Request $request, string $token): JsonResponse
    {
        $returnRecord = ReturnRecord::with(['item', 'claim'])
            ->where('confirmation_token', $token)
            ->first();

        if (! $returnRecord) {
            return response()->json([
                'message' => 'Invalid confirmation link or already confirmed.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($returnRecord->confirmation_token_expires_at && $returnRecord->confirmation_token_expires_at->isPast()) {
            return response()->json([
                'message' => 'This confirmation link has expired (48h window). Please contact campus security.',
            ], JsonResponse::HTTP_GONE);
        }

        if ($returnRecord->recipient_confirmed || $returnRecord->confirmed_at) {
            return response()->json([
                'message' => 'Collection has already been confirmed.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($returnRecord, $request) {
            $returnRecord->update([
                'recipient_confirmed' => true,
                'confirmed_at' => now(),
                'confirmation_token' => null, // single-use protection against replay (FR-44)
            ]);

            AuditLogger::log('return.recipient_confirmed_link', $returnRecord, null, [
                'confirmed_via' => 'secure_link',
                'ip' => $request->ip(),
                'confirmed_at' => now()->toISOString(),
            ]);
        });

        if ($returnRecord->handed_over_by) {
            $item = $returnRecord->item;
            app(NotificationService::class)->send(new NotificationData(
                userId:  $returnRecord->handed_over_by,
                type:    'return_confirmed',
                payload: [
                    'return_id'      => $returnRecord->id,
                    'item_id'        => $returnRecord->claim?->item_id,
                    'reference_code' => $item?->reference_code,
                    'title'          => $item?->title ?? 'Item',
                    'message'        => "Recipient confirmed physical handover for item \"{$item?->title}\" (Ref: {$item?->reference_code}).",
                ]
            ));
        }

        return response()->json([
            'message' => 'Collection confirmed successfully. Thank you!',
            'data' => new ReturnResource($returnRecord->fresh(['item', 'claim', 'recipient', 'staff', 'storageLocation'])),
        ]);
    }

    /**
     * FR-44: Recipient confirms physical collection (authenticated).
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $returnRecord = ReturnRecord::findOrFail($id);
        $user = $request->user();

        // Only the recipient or admin can confirm
        if ($returnRecord->returned_to !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Only the recipient can confirm collection.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        if ($returnRecord->recipient_confirmed || $returnRecord->confirmed_at) {
            return response()->json([
                'message' => 'Collection has already been confirmed.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($returnRecord, $user) {
            $returnRecord->update([
                'recipient_confirmed' => true,
                'confirmed_at' => now(),
                'confirmation_token' => null,
            ]);

            AuditLogger::log('return.recipient_confirmed', $returnRecord, null, [
                'confirmed_by' => $user->id,
                'confirmed_at' => now()->toISOString(),
            ], $user);
        });

        return response()->json([
            'message' => 'Collection confirmed successfully.',
            'data' => new ReturnResource($returnRecord->fresh(['item', 'claim', 'recipient', 'staff', 'storageLocation'])),
        ]);
    }

    /**
     * FR-46: Export returns as CSV with optional date-range filter.
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('viewAny', ReturnRecord::class);

        $query = ReturnRecord::with(['claim.item', 'recipient', 'staff'])
            ->orderByDesc('return_date');

        if ($dateFrom = $request->query('date_from')) {
            $query->where('return_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $query->where('return_date', '<=', $dateTo);
        }

        $returns = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="returns_' . date('Y-m-d_His') . '.csv"',
        ];

        return response()->stream(function () use ($returns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Item Reference', 'Item Title', 'Recipient', 'Staff', 'Return Date', 'Condition', 'Confirmed At']);

            foreach ($returns as $ret) {
                $item = $ret->claim?->item;
                fputcsv($handle, [
                    $ret->id,
                    $item?->reference_code ?? 'N/A',
                    $item?->title ?? 'N/A',
                    $ret->recipient?->full_name ?? 'N/A',
                    $ret->staff?->full_name ?? 'N/A',
                    $ret->return_date?->format('Y-m-d') ?? (string) $ret->return_date,
                    $ret->condition_on_return,
                    $ret->confirmed_at ? $ret->confirmed_at->format('Y-m-d H:i:s') : ($ret->recipient_confirmed ? 'Yes' : 'Pending'),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
