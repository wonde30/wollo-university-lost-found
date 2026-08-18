<?php

namespace Tests\Feature\Items;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Location;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateFoundItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_register_found_item_into_storage(): void
    {
        $user = User::factory()->staff()->create();
        Sanctum::actingAs($user);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Cafeteria', 'code' => 'LOC-CAF-01', 'zone' => 'cafeteria']);
        $category = Category::create(['name' => 'IDs & Cards', 'name_am' => 'መታወቂያ']);
        $storage = StorageLocation::create(['campus_id' => $campus->id, 'name' => 'Security Safe A', 'code' => 'SEC-SAFE-A']);

        $response = $this->postJson('/api/v1/items/found', [
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'storage_location_id' => $storage->id,
            'title' => 'Found Student ID Card',
            'description' => 'Student ID found near counter with green lanyard and photo intact',
            'incident_date' => now()->format('Y-m-d'),
            'held_at' => 'security_office',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'found')
            ->assertJsonPath('data.status', 'found_unclaimed');

        $this->assertDatabaseHas('items', [
            'title' => 'Found Student ID Card',
            'type' => 'found',
            'status' => 'found_unclaimed',
            'held_at' => 'security_office',
        ]);

        $this->assertDatabaseHas('custody_events', [
            'storage_location_id' => $storage->id,
            'event_type' => 'deposited',
        ]);
    }
}
