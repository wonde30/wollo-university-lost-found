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
        $category = Category::firstOrCreate(['name' => 'Calculators'], ['name_am' => 'ካልኩሌተር']);

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

    public function test_guest_can_browse_lost_and_found_items_and_filter_by_type(): void
    {
        $reporter = User::factory()->create();
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::firstOrCreate(['name' => 'Electronics'], ['name_am' => 'ኤሌክትሮኒክስ']);

        // Create Lost Item
        Item::create([
            'reference_code' => 'WU-LOST9999',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Lost Black Wallet',
            'description' => 'Leather wallet lost in cafeteria',
            'incident_date' => now(),
        ]);

        // Create Found Item
        Item::create([
            'reference_code' => 'WU-FND9999',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Blue Umbrella',
            'description' => 'Umbrella found near gate',
            'incident_date' => now(),
        ]);

        // Browse all
        $allResponse = $this->getJson('/api/v1/public/items');
        $allResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Filter lost
        $lostResponse = $this->getJson('/api/v1/public/items?type=lost');
        $lostResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_code', 'WU-LOST9999');

        // Filter found
        $foundResponse = $this->getJson('/api/v1/public/items?type=found');
        $foundResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_code', 'WU-FND9999');
    }
}
