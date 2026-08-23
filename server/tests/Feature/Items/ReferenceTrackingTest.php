<?php

declare(strict_types=1);

namespace Tests\Feature\Items;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_track_item_by_reference_code(): void
    {
        $user = User::factory()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::create(['name' => 'Books']);

        $item = Item::create([
            'reference_code' => 'WU-2024-TRACK01',
            'reporter_id' => $user->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Lost Scientific Calculator',
            'description' => 'Black Casio scientific calculator left on study desk',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson("/api/v1/public/track/{$item->reference_code}");

        $response->assertOk()
            ->assertJsonPath('reference_code', 'WU-2024-TRACK01')
            ->assertJsonPath('status', 'lost');
    }
}
