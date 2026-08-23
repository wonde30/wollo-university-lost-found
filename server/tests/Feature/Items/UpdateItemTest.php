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

class UpdateItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_update_their_item(): void
    {
        $user = User::factory()->student()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::create(['name' => 'Books']);

        $item = Item::create([
            'reference_code' => 'WU-2024-UPD01',
            'reporter_id' => $user->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Lost Textbook',
            'description' => 'Calculus textbook with blue cover',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        $response = $this->putJson("/api/v1/items/{$item->id}", [
            'title' => 'Lost Calculus 8th Edition Textbook',
            'description' => 'Updated description with blue cover and notes inside',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Lost Calculus 8th Edition Textbook');

        $this->assertEquals('Lost Calculus 8th Edition Textbook', $item->fresh()->title);
    }
}
