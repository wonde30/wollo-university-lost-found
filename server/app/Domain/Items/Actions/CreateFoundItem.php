<?php

namespace App\Domain\Items\Actions;

use App\Domain\Items\DTOs\CreateItemData;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Models\ItemTag;
use App\Support\Enums\CustodyEventType;
use App\Support\Enums\ItemHeldAt;
use App\Support\Enums\ItemStatus;
use App\Support\Enums\ItemType;
use App\Support\Helpers\ReferenceCode;
use Illuminate\Support\Facades\DB;

class CreateFoundItem
{
    public function execute(CreateItemData $data): Item
    {
        return DB::transaction(function () use ($data) {
            $heldAt = $data->heldAt ?? ItemHeldAt::SECURITY_OFFICE->value;

            $item = Item::create([
                'reference_code' => ReferenceCode::generate('WU'),
                'reporter_id' => $data->userId,
                'campus_id' => $data->campusId ?? 1,
                'category_id' => $data->categoryId,
                'location_id' => $data->locationId,
                'location_detail' => $data->locationDetail,
                'type' => ItemType::FOUND,
                'status' => ItemStatus::FOUND_UNCLAIMED,
                'held_at' => $heldAt,
                'title' => $data->title,
                'description' => $data->description,
                'incident_date' => $data->incidentDate,
                'incident_time' => $data->incidentTime,
                'brand' => $data->brand,
                'color' => $data->color,
                'serial_number' => $data->serialNumber,
                'estimated_value' => $data->estimatedValue,
                'is_high_value' => $data->isHighValue,
                'last_activity_at' => now(),
            ]);

            foreach ($data->tags as $tag) {
                ItemTag::create([
                    'item_id' => $item->id,
                    'tag' => $tag,
                ]);
            }

            if ($data->storageLocationId) {
                CustodyEvent::create([
                    'item_id' => $item->id,
                    'actor_id' => $data->userId,
                    'storage_location_id' => $data->storageLocationId,
                    'event_type' => CustodyEventType::DEPOSITED,
                    'condition' => 'good',
                    'notes' => 'Initial found item storage intake',
                ]);
            }

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $data->userId,
                'from_status' => null,
                'to_status' => ItemStatus::FOUND_UNCLAIMED->value,
                'changed_by_role' => 'staff',
                'note' => 'Found item registered and placed in custody',
            ]);

            return $item;
        });
    }
}
