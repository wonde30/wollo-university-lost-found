<?php

namespace Tests\Feature\Items;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossLinkEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_link_eligibility_detects_own_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::create(['name' => 'Laptops', 'name_am' => 'ላፕቶፕ']);

        $lostItem = Item::create([
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'title' => 'Lost HP Laptop',
            'description' => 'HP laptop left in library',
            'incident_date' => now()->toDateString(),
            'status' => 'lost',
        ]);

        $response = $this->postJson('/api/v1/items/cross-link-check', [
            'target_item_id' => $lostItem->id,
            'action' => 'report_found',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => false,
                'is_own_item' => true,
            ]);
    }

    public function test_cross_link_eligibility_detects_existing_report_conflict(): void
    {
        $reporterA = User::factory()->create();
        $userB = User::factory()->create();
        $this->actingAs($userB);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::create(['name' => 'Phones', 'name_am' => 'ስልክ']);

        $lostItem = Item::create([
            'reporter_id' => $reporterA->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'title' => 'Lost iPhone 13',
            'description' => 'Black iPhone 13 in black case',
            'incident_date' => now()->toDateString(),
            'status' => 'lost',
        ]);

        $existingFound = Item::create([
            'reporter_id' => $userB->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'title' => 'Found iPhone',
            'description' => 'Black phone found in cafeteria',
            'incident_date' => now()->toDateString(),
            'status' => 'found_unclaimed',
        ]);

        $response = $this->postJson('/api/v1/items/cross-link-check', [
            'target_item_id' => $lostItem->id,
            'action' => 'report_found',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => true,
                'is_own_item' => false,
                'has_existing' => true,
                'existing_id' => $existingFound->id,
            ]);
    }
}
