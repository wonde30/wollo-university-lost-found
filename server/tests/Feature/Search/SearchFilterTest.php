<?php

namespace Tests\Feature\Search;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_search_items_with_filters(): void
    {
        $user = User::factory()->create();

        $campus = Campus::create(['name' => 'Dessie Campus', 'short_code' => 'DC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Gate', 'code' => 'LOC-GATE-DC', 'zone' => 'gate']);
        $category = Category::create(['name' => 'Books & Notes', 'name_am' => 'መጽሐፍ']);

        Item::create([
            'reference_code' => 'WU-BOOK0123',
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Calculus Textbook 8th Edition',
            'description' => 'Hardcover James Stewart Calculus book found on bench',
            'incident_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/public/items?query=Calculus&category_id=' . $category->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Calculus Textbook 8th Edition')
            ->assertJsonPath('data.0.category.name', 'Books & Notes');
    }
}
