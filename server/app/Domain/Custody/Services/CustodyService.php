<?php

namespace App\Domain\Custody\Services;

use App\Models\CustodyEvent;
use App\Models\StorageLocation;

class CustodyService
{
    public function logEvent(int $itemId, int $userId, string $type, ?int $storageId = null, ?string $notes = null): CustodyEvent
    {
        return CustodyEvent::create([
            'item_id' => $itemId,
            'performed_by_user_id' => $userId,
            'storage_location_id' => $storageId,
            'event_type' => $type,
            'notes' => $notes,
        ]);
    }
}
