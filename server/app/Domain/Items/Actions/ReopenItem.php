<?php

namespace App\Domain\Items\Actions;

use App\Models\Item;
use App\Models\User;
use App\Support\Enums\ItemStatus;

class ReopenItem
{
    public function execute(Item $item, ?User $user = null): Item
    {
        $changeStatus = new ChangeItemStatus();
        $targetStatus = $item->type === 'lost' ? ItemStatus::LOST : ItemStatus::FOUND_UNCLAIMED;
        return $changeStatus->execute($item, $targetStatus, 'Reopened report', $user);
    }
}
