<?php

namespace App\Domain\Items\Services;

use App\Models\Item;

class DuplicateDetectionService
{
    /**
     * FR-63: On submission, check if same category + campus location has a report within 7 days.
     * Returns the matching item if found, or null.
     */
    public function check(int $categoryId, ?int $campusId = null, ?string $serialNumber = null): ?Item
    {
        // Check serial number first — exact match across all items
        if (!empty($serialNumber)) {
            $bySerial = Item::where('serial_number', $serialNumber)
                ->where('is_deleted', false)
                ->first();
            if ($bySerial) {
                return $bySerial;
            }
        }

        $query = Item::where('category_id', $categoryId)
            ->where('is_deleted', false)
            ->whereIn('status', ['lost', 'found_unclaimed'])
            ->where('created_at', '>=', now()->subDays(7));

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        return $query->first();
    }
}

