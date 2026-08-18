<?php

namespace App\Domain\Items\Actions;

use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Support\Enums\ItemStatus;
use App\Support\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ChangeItemStatus
{
    public function execute(Item $item, string|ItemStatus $newStatus, ?string $note = null): Item
    {
        $statusValue = $newStatus instanceof ItemStatus ? $newStatus->value : $newStatus;
        $fromStatus = $item->status instanceof ItemStatus ? $item->status->value : (string) $item->status;

        return DB::transaction(function () use ($item, $statusValue, $fromStatus, $note) {
            $user = auth()->user();
            $userRole = $user ? ($user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role) : 'system';

            $item->status = $statusValue;
            $item->last_activity_at = now();
            $item->save();

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user?->id,
                'from_status' => $fromStatus,
                'to_status' => $statusValue,
                'changed_by_role' => $userRole,
                'note' => $note,
                'ip_address' => Request::ip(),
            ]);

            AuditLogger::log('item.status_changed', $item, ['status' => $fromStatus], ['status' => $statusValue], $user);

            return $item;
        });
    }
}
