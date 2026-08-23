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

class RejectClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_reject_claim(): void
    {
        $staff = User::factory()->staff()->create();
        $student = User::factory()->student()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CLAIM01',
            'reporter_id' => $staff->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Smartphone',
            'description' => 'Black Samsung smartphone found on library desk',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $student->id,
            'status' => 'pending',
            'explanation' => 'This is my Samsung galaxy phone with cracked screen.',
        ]);

        $this->actingAs($staff);

        $response = $this->postJson("/api/v1/claims/{$claim->id}/review", [
            'status' => 'rejected',
            'review_note' => 'Serial number and unique identifiers do not match.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'rejected',
            'reviewed_by' => $staff->id,
        ]);
    }
}
