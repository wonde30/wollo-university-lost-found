<?php

namespace Tests\Feature\Auth;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_personal_statistics_summary(): void
    {
        $user = User::factory()->create();
        $staff = User::factory()->staff()->create();

        $campus = Campus::create([
            'name' => 'Meda Campus',
            'short_code' => 'MDC',
            'city' => 'Dessie',
            'region' => 'Amhara',
        ]);
        $location = Location::create([
            'campus_id' => $campus->id,
            'name' => 'Main Gate',
            'code' => 'LOC-MEDA-01',
            'zone' => 'gate',
        ]);
        $category = Category::create([
            'name' => 'Electronics',
            'name_am' => 'ኤሌክትሮኒክስ',
        ]);

        Item::create([
            'reference_code' => 'WU-LOST9901',
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Lost Phone',
            'description' => 'Black Samsung Galaxy S21',
            'incident_date' => now(),
            'is_deleted' => false,
        ]);

        Item::create([
            'reference_code' => 'WU-FND9901',
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Notebook',
            'description' => 'Blue notebook found in library',
            'incident_date' => now(),
            'is_deleted' => false,
        ]);

        $foundItem = Item::create([
            'reference_code' => 'WU-FND9902',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Watch',
            'description' => 'Silver Casio wrist watch',
            'incident_date' => now(),
            'is_deleted' => false,
        ]);

        Claim::create([
            'item_id' => $foundItem->id,
            'claimant_id' => $user->id,
            'explanation' => 'This is my silver Casio watch with small scratch on the back glass.',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/profile/summary');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'my_lost_count' => 1,
                    'my_found_count' => 1,
                    'my_claims_count' => 1,
                    'active_claims_count' => 1,
                ],
            ]);
    }
}
