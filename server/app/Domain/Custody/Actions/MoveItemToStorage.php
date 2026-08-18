<?php

namespace App\Domain\Custody\Actions;

use App\Domain\Custody\DTOs\CustodyEventData;
use App\Models\CustodyEvent;

class MoveItemToStorage
{
    public function execute(int $itemId, int $storageLocationId, int $officerUserId, ?string $notes = null): CustodyEvent
    {
        $action = new RecordCustodyEvent();
        return $action->execute(new CustodyEventData(
            itemId: $itemId,
            performedByUserId: $officerUserId,
            eventType: 'moved',
            storageLocationId: $storageLocationId,
            notes: $notes ?? 'Moved within storage'
        ));
    }
}
