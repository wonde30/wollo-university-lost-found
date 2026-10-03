<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SMELL-07 / Lifecycle integrity fix:
     *
     * In the seeded database, 4 items were marked with status 'found_claimed'
     * (a non-canonical status not defined in the 7-state lifecycle machine):
     *
     * - Items with an approved claim (e.g. Item #10) should be 'claimed'.
     * - Items whose claims are only pending or rejected (e.g. Items #5, #16, #21)
     *   should be 'found_unclaimed' because ownership has not been verified.
     */
    public function up(): void
    {
        // 1. Items with an approved claim -> 'claimed'
        DB::table('items')
            ->where('status', 'found_claimed')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('claims')
                    ->whereColumn('claims.item_id', 'items.id')
                    ->where('claims.status', 'approved');
            })
            ->update([
                'status'     => 'claimed',
                'updated_at' => now(),
            ]);

        // 2. Items without an approved claim -> 'found_unclaimed'
        DB::table('items')
            ->where('status', 'found_claimed')
            ->update([
                'status'     => 'found_unclaimed',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Not reversible — data normalisation
    }
};
