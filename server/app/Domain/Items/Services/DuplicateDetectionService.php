<?php

namespace App\Domain\Items\Services;

use App\Models\Item;

class DuplicateDetectionService
{
    public function check(string $title, int $categoryId, int $locationId, ?string $serialNumber = null): ?Item
    {
        if (!empty($serialNumber)) {
            $bySerial = Item::where('serial_number', $serialNumber)->first();
            if ($bySerial) {
                return $bySerial;
            }
        }

        return Item::where('category_id', $categoryId)
            ->where('location_id', $locationId)
            ->where('title', 'like', "%{$title}%")
            ->whereIn('status', ['open', 'in_storage'])
            ->first();
    }
}
