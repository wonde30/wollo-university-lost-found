<?php

declare(strict_types=1);

namespace Tests\Feature\Claims;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_submit_duplicate_pending_claim_on_same_item(): void
    {
        $student = User::factory()->student()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CLAIM03',
            'reporter_id' => $student->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Smartphone',
            'description' => 'Black Samsung smartphone found on library desk',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $student->id,
            'status' => 'pending',
            'explanation' => 'First claim explanation text.',
        ]);

        $this->actingAs($student);

        $response = $this->postJson('/api/v1/claims', [
            'item_id' => $item->id,
            'explanation' => 'Second claim attempt on same item.',
        ]);

        $response->assertStatus(422);
    }
}
