<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Items\ChangeItemStatusRequest;
use App\Http\Resources\Api\V1\ItemResource;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemStatusController extends Controller
{
    public function update(ChangeItemStatusRequest $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $this->authorize('changeStatus', $item);

        $user = $request->user();
        $previous = (string) $item->status;
        $newStatus = $request->status;
        $note = $request->reason ?? $request->notes;

        // FR-26: Admin reopen logic requires a mandatory note
        if (in_array($previous, ['withdrawn', 'closed', 'expired']) && in_array($newStatus, ['lost', 'found_unclaimed'])) {
            if (!$user->isAdmin()) {
                return response()->json([
                    'message' => 'Only administrators can reopen a closed/withdrawn/expired item.',
                ], JsonResponse::HTTP_FORBIDDEN);
            }
            if (empty(trim((string)$note))) {
                return response()->json([
                    'message' => 'A mandatory note is required when reopening an item.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $finalNote = $note ?: 'Status changed';

        DB::transaction(function () use ($item, $newStatus, $previous, $finalNote, $user, $request) {
            $item->update([
                'status' => $newStatus,
                'last_activity_at' => now(),
            ]);

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => $previous,
                'to_status' => $newStatus,
                'changed_by_role' => $user->getRoleName(),
                'note' => $finalNote,
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.status_changed', $item, ['status' => $previous], ['status' => $newStatus, 'note' => $finalNote], $user);
        });

        if ($item->reporter_id && $item->reporter_id !== $user->id) {
            app(\App\Domain\Notifications\Services\NotificationService::class)->send(new \App\Domain\Notifications\DTOs\NotificationData(
                userId:  $item->reporter_id,
                type:    'item_status_changed',
                payload: [
                    'item_id'         => $item->id,
                    'reference_code'  => $item->reference_code,
                    'title'           => $item->title,
                    'previous_status' => $previous,
                    'new_status'      => $newStatus,
                    'message'         => "The status of your reported item \"{$item->title}\" (Ref: {$item->reference_code}) was updated to {$newStatus}.",
                ]
            ));
        }

        return response()->json([
            'message' => __('items.status_changed'),
            'data' => new ItemResource($item),
        ]);
    }
}

