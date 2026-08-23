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

class ReverseClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reverse_approved_claim(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-2024-CLAIM02',
            'reporter_id' => $admin->id,
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'claimed',
            'title' => 'Found Smartphone',
            'description' => 'Black Samsung smartphone found on library desk',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $student->id,
            'status' => 'approved',
            'explanation' => 'This is my Samsung galaxy phone with cracked screen.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/v1/claims/{$claim->id}/reverse", [
            'review_note' => 'Mistaken approval; genuine owner provided original purchase receipt.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertEquals('rejected', $claim->fresh()->status->value);
        $this->assertEquals('found_unclaimed', $item->fresh()->status->value);
    }
}
