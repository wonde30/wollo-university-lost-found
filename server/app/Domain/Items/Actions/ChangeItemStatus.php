<?php

declare(strict_types=1);

namespace App\Domain\Items\Actions;

use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Support\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ChangeItemStatus
{
    public function execute(Item $item, string $newStatus, ?string $note = null): Item
    {
        $statusValue = (string) $newStatus;
        $fromStatus = (string) $item->status;

        return DB::transaction(function () use ($item, $statusValue, $fromStatus, $note) {
            $user = auth()->user();
            $userRole = $user ? ($user->getRoleName()) : 'system';

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
