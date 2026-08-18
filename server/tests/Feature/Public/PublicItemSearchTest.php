<?php

namespace Tests\Feature\Public;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicItemSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_unclaimed_found_items_with_zero_pii(): void
    {
        $reporter = User::factory()->create([
            'full_name' => 'Secret Reporter',
            'email' => 'secret@wollo.edu.et',
            'phone' => '+251911223344',
        ]);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Keys'], ['name_am' => 'ቁልፍ']);

        $item = Item::create([
            'reference_code' => 'WU-KEYS1234',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Set of Dormitory Room Keys',
            'description' => 'Set of 3 keys with blue Wollo University plastic keychain attached',
            'incident_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/public/items');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.reference_code', 'WU-KEYS1234')
            ->assertJsonMissingPath('data.0.reporter')
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.phone');
    }

    public function test_guest_can_track_item_via_reference_code(): void
    {
        $reporter = User::factory()->create();
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::create(['name' => 'Calculators', 'name_am' => 'ካልኩሌተር']);

        Item::create([
            'reference_code' => 'WU-CALC1234',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Casio Scientific Calculator',
            'description' => 'Black Casio fx-991EX scientific calculator with solar panel',
            'incident_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/public/track/WU-CALC1234');

        $response->assertStatus(200)
            ->assertJson([
                'reference_code' => 'WU-CALC1234',
                'title' => 'Casio Scientific Calculator',
                'category' => 'Calculators',
                'status' => 'lost',
            ]);
    }
}
