<?php

namespace App\Domain\Custody\Actions;

use App\Domain\Custody\DTOs\CustodyEventData;
use App\Models\CustodyEvent;

class ReleaseItemFromStorage
{
    public function execute(int $itemId, int $officerUserId, ?string $notes = null): CustodyEvent
    {
        $action = new RecordCustodyEvent();
        return $action->execute(new CustodyEventData(
            itemId: $itemId,
            performedByUserId: $officerUserId,
            eventType: 'checked_out',
            notes: $notes ?? 'Released from storage'
        ));
    }
}
