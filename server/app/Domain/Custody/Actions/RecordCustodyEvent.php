<?php

namespace App\Domain\Custody\Actions;

use App\Domain\Custody\DTOs\CustodyEventData;
use App\Models\CustodyEvent;
use App\Models\StorageLocation;
use Illuminate\Support\Facades\DB;

class RecordCustodyEvent
{
    public function execute(CustodyEventData $data): CustodyEvent
    {
        return DB::transaction(function () use ($data) {
            $event = CustodyEvent::create([
                'item_id' => $data->itemId,
                'performed_by_user_id' => $data->performedByUserId,
                'storage_location_id' => $data->storageLocationId,
                'event_type' => $data->eventType,
                'notes' => $data->notes,
                'custody_proof_url' => $data->custodyProofUrl,
            ]);

            if ($data->storageLocationId) {
                StorageLocation::where('id', $data->storageLocationId)->increment('current_occupancy');
            }

            return $event;
        });
    }
}
