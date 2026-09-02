<?php

namespace Tests\Feature\Returns;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_process_item_return(): void
    {
        $staff = User::factory()->staff()->create();
        $owner = User::factory()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Gate 1', 'code' => 'LOC-GATE-01', 'zone' => 'gate']);
        $storage = StorageLocation::create(['campus_id' => $campus->id, 'name' => 'Security Safe A', 'code' => 'SEC-SAFE-A']);
        $category = Category::create(['name' => 'Phones', 'name_am' => 'ስልክ']);

        $item = Item::create([
            'reference_code' => 'WU-PHN01234',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'claimed',
            'title' => 'Samsung Galaxy S22',
            'description' => 'Found phone in cafeteria',
            'incident_date' => now(),
        ]);

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $owner->id,
            'status' => 'approved',
            'explanation' => 'My personal black Samsung phone with transparent protective case',
        ]);

        $this->actingAs($staff);

        $response = $this->postJson('/api/v1/returns', [
            'item_id' => $item->id,
            'claim_id' => $claim->id,
            'returned_to' => $owner->id,
            'storage_location_id' => $storage->id,
            'return_date' => now()->format('Y-m-d'),
            'condition_on_return' => 'good',
            'notes' => 'Returned after unlocking with pin code and ID verification',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.condition_on_return', 'good');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'returned',
        ]);

        $this->assertDatabaseHas('returns', [
            'claim_id' => $claim->id,
            'returned_to' => $owner->id,
            'handed_over_by' => $staff->id,
        ]);
    }
}
