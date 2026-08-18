<?php

namespace App\Domain\Items\Actions;

use App\Models\Item;
use App\Models\User;

class WithdrawItem
{
    public function execute(Item $item, ?User $user = null): Item
    {
        $changeStatus = new ChangeItemStatus();
        return $changeStatus->execute($item, 'withdrawn', 'Withdrawn by owner/finder', $user);
    }
}
