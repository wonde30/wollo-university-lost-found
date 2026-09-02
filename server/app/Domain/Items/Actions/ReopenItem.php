<?php

declare(strict_types=1);

namespace App\Domain\Items\Actions;

use App\Models\Item;
use App\Models\User;

class ReopenItem
{
    public function execute(Item $item, ?User $user = null): Item
    {
        $changeStatus = new ChangeItemStatus();
        $targetStatus = $item->type === 'lost' ? 'lost' : 'found_unclaimed';
        return $changeStatus->execute($item, $targetStatus, 'Reopened report', $user);
    }
}
