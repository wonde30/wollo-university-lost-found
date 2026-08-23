<?php

declare(strict_types=1);

namespace Tests\Feature\Custody;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_move_item_to_new_storage_location(): void
    {
        $staff = User::factory()->staff()->create();
        $this->actingAs($staff);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Security Office', 'code' => 'LOC-SEC-01', 'zone' => 'administrative']);
        $storageA = StorageLocation::create(['campus_id' => $campus->id, 'name' => 'Locker A', 'code' => 'SL-LCK-A']);
        $storageB = StorageLocation::create(['campus_id' => $campus->id, 'name' => 'Locker B', 'code' => 'SL-LCK-B']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CUST02',
            'reporter_id' => $staff->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Watch',
            'description' => 'Silver wristwatch found near entrance',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->postJson("/api/v1/custody/items/{$item->id}/move", [
            'storage_location_id' => $storageB->id,
            'notes' => 'Moved to secure vault',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.storage_location_id', $storageB->id);
    }
}
