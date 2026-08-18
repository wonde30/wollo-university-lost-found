<?php

namespace App\Domain\Items\Actions;

use App\Domain\Items\DTOs\UpdateItemData;
use App\Domain\Items\Exceptions\ItemNotEditableException;
use App\Models\Item;

class UpdateItem
{
    public function execute(Item $item, UpdateItemData $data): Item
    {
        if (in_array($item->status, ['returned', 'disposed'])) {
            throw new ItemNotEditableException();
        }

        $attributes = array_filter((array) $data, fn($v) => !is_null($v) && !is_array($v));
        $item->update($attributes);

        return $item;
    }
}
