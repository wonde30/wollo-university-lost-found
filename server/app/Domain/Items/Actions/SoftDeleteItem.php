<?php

namespace App\Domain\Items\Actions;

use App\Models\Item;

class SoftDeleteItem
{
    public function execute(Item $item): bool
    {
        return (bool) $item->delete();
    }
}
