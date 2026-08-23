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

class RecordCustodyEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_custody_event(): void
    {
        $staff = User::factory()->staff()->create();
        $this->actingAs($staff);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Security Office', 'code' => 'LOC-SEC-01', 'zone' => 'administrative']);
        $storage = StorageLocation::create(['campus_id' => $campus->id, 'name' => 'Locker A', 'code' => 'SL-LCK-A']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CUST01',
            'reporter_id' => $staff->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Calculator',
            'description' => 'Black Casio scientific calculator found in office',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->postJson('/api/v1/custody', [
            'item_id' => $item->id,
            'event_type' => 'deposited',
            'storage_location_id' => $storage->id,
            'notes' => 'Received from cleaner',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.item_id', $item->id);

        $this->assertDatabaseHas('custody_events', [
            'item_id' => $item->id,
            'event_type' => 'deposited',
            'storage_location_id' => $storage->id,
        ]);
    }
}
