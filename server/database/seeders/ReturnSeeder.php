<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReturnSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->all();
        $staffUser = User::whereHas('role', fn ($q) => $q->whereIn('name', ['staff', 'admin']))->first() ?? User::first();
        if (empty($users) || ! $staffUser) {
            return;
        }
        $staffId = $staffUser->id;
        $storageLocations = StorageLocation::pluck('id')->all();

        // Get returned items
        $returnedItems = Item::where('status', 'returned')
            ->whereDoesntHave('claims.returnRecord')
            ->get();

        foreach ($returnedItems as $item) {
            $claimantId = $users[array_rand($users)];
            // Avoid claimant being the reporter for consistency
            if (count($users) > 1 && $claimantId === $item->reporter_id) {
                $filtered = array_filter($users, fn ($id) => $id !== $item->reporter_id);
                $claimantId = $filtered[array_rand($filtered)];
            }

            $claimCreatedAt = $item->created_at->copy()->addDays(rand(1, 7));
            $returnDate = $claimCreatedAt->copy()->addDays(rand(1, 5));

            $claim = Claim::create([
                'item_id'       => $item->id,
                'claimant_id'   => $claimantId,
                'explanation'   => "I lost this item at {$item->location_detail}. I have full ownership proof.",
                'status'        => 'approved',
                'reviewed_by'   => $staffId,
                'review_note'   => 'Ownership verified with matching student credentials and item photos.',
                'reviewed_at'   => $returnDate->copy()->subHours(2),
                'auto_rejected' => false,
                'created_at'    => $claimCreatedAt,
                'updated_at'    => $returnDate,
            ]);

            $storageId = ! empty($storageLocations) ? $storageLocations[array_rand($storageLocations)] : null;

            ReturnRecord::create([
                'claim_id'            => $claim->id,
                'returned_to'         => $claimantId,
                'handed_over_by'      => $staffId,
                'storage_location_id' => $storageId,
                'return_date'         => $returnDate->toDateString(),
                'return_time'         => sprintf('%02d:%02d:00', rand(9, 17), rand(0, 59)),
                'condition_on_return' => 'excellent',
                'notes'               => 'Item handed over to student owner in person. Verified ID and confirmation code.',
                'recipient_confirmed' => true,
                'confirmed_at'        => $returnDate,
                'created_at'          => $returnDate,
                'updated_at'          => $returnDate,
            ]);
        }
    }
}
