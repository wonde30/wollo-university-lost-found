<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Claim;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicRbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CampusSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_dynamic_custom_role_lifecycle_and_permission_removal_403(): void
    {
        // 1. Admin logs in and creates a custom role 'claims_officer'
        $admin = User::factory()->admin()->create([
            'is_active' => true,
        ]);

        $reviewClaimPerm = Permission::where('name', 'REVIEW_CLAIMS')->firstOrFail();
        $reverseClaimPerm = Permission::where('name', 'REVERSE_CLAIMS')->firstOrFail();

        $customRole = Role::create([
            'name' => 'claims_officer',
            'display_name' => 'Claims Officer',
            'is_system' => false,
            'is_active' => true,
        ]);

        // 2. Assigns REVIEW_CLAIMS permission to custom role
        $customRole->permissions()->sync([$reviewClaimPerm->id]);

        // 3. Assign role to a new user
        $officer = User::factory()->create([
            'role_id' => $customRole->id,
            'is_active' => true,
        ]);

        // Verify user has permission dynamically
        $this->assertTrue($officer->hasPermission('REVIEW_CLAIMS'));
        $this->assertFalse($officer->hasPermission('REVERSE_CLAIMS'));

        $campus = \App\Models\Campus::firstOrFail();
        $category = \App\Models\Category::firstOrFail();

        // 4. Create an item and a pending claim
        $claimant = User::factory()->student()->create(['is_active' => true]);
        $reporter = User::factory()->student()->create(['is_active' => true]);
        $item = Item::create([
            'reference_code' => 'WU-TEST-F1',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Test Found Item',
            'description' => 'Test description for item',
            'incident_date' => now()->subDay(),
        ]);
        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'pending',
            'explanation' => 'This is my item',
        ]);

        // 5. Officer logs in and reviews claim -> MUST BE AUTHORIZED (200 OK)
        $response = $this->actingAs($officer)->postJson("/api/v1/claims/{$claim->id}/review", [
            'status' => 'approved',
            'review_note' => 'Approved by claims officer with dynamic permission.',
        ]);

        $response->assertOk();
        $this->assertEquals('approved', (string) $claim->fresh()->status);

        // 6. Now Admin removes REVIEW_CLAIMS permission from the custom role
        $customRole->permissions()->detach($reviewClaimPerm->id);
        $officer = $officer->fresh();

        $this->assertFalse($officer->hasPermission('REVIEW_CLAIMS'));

        // 7. Create another pending claim
        $item2 = Item::create([
            'reference_code' => 'WU-TEST-F2',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Test Found Item 2',
            'description' => 'Test description for item 2',
            'incident_date' => now()->subDay(),
        ]);
        $claim2 = Claim::create([
            'item_id' => $item2->id,
            'claimant_id' => $claimant->id,
            'status' => 'pending',
            'explanation' => 'This is my item 2',
        ]);

        // 8. Officer attempts to review claim -> MUST BE REJECTED WITH 403 FORBIDDEN
        $unauthorizedResponse = $this->actingAs($officer)->postJson("/api/v1/claims/{$claim2->id}/review", [
            'status' => 'approved',
            'review_note' => 'Attempting review without permission.',
        ]);

        $unauthorizedResponse->assertStatus(403);
    }
}
