<?php

namespace Tests\Feature\Claims;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_claim_for_found_item(): void
    {
        $staff = User::factory()->staff()->create();
        $claimant = User::factory()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Gate 1', 'code' => 'LOC-GATE-01', 'zone' => 'gate']);
        $category = Category::create(['name' => 'Wallets', 'name_am' => 'ቦርሳ']);

        $item = Item::create([
            'reference_code' => 'WU-WLLT1234',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Brown Leather Wallet',
            'description' => 'Found with some cards inside near security checkpoint',
            'incident_date' => now(),
        ]);

        $this->actingAs($claimant);

        $response = $this->postJson('/api/v1/claims', [
            'item_id' => $item->id,
            'explanation' => 'This is my personal brown leather wallet which contains my student ID card and Ethiopian National ID card.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('claims', [
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'pending',
        ]);
    }
}
