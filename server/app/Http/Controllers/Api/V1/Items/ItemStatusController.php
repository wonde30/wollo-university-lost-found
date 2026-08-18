<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Items\ChangeItemStatusRequest;
use App\Http\Resources\Api\V1\ItemResource;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use Illuminate\Http\JsonResponse;

class ItemStatusController extends Controller
{
    public function update(ChangeItemStatusRequest $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $this->authorize('changeStatus', $item);

        $user = $request->user();
        $previous = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
        $newStatus = $request->status;

        $item->update([
            'status' => $newStatus,
            'last_activity_at' => now(),
        ]);

        ItemStatusHistory::create([
            'item_id' => $item->id,
            'changed_by' => $user->id,
            'from_status' => $previous,
            'to_status' => $newStatus,
            'changed_by_role' => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role,
            'note' => $request->reason ?? $request->notes ?? 'Status changed',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => __('items.status_changed'),
            'data' => new ItemResource($item),
        ]);
    }
}
