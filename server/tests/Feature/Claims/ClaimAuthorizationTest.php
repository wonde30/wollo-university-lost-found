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

class ClaimAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_review_claim(): void
    {
        $student = User::factory()->student()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CLAIM04',
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

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $student->id,
            'status' => 'pending',
            'explanation' => 'Claim explanation text.',
        ]);

        $this->actingAs($student);

        $response = $this->postJson("/api/v1/claims/{$claim->id}/review", [
            'decision' => 'approved',
        ]);

        $response->assertForbidden();
    }
}
