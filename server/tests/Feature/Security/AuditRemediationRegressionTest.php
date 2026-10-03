<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditRemediationRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * AUTH-03: Admin-created user can authenticate (password is not double-hashed).
     */
    public function test_auth_03_admin_created_user_can_log_in(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $studentRole = Role::where('name', 'student')->firstOrFail();

        $res = $this->actingAs($admin)->postJson('/api/v1/admin/users', [
            'full_name' => 'Newly Created Student',
            'university_id' => 'WU-STD-2026-9999',
            'email' => 'createdstudent@wu.edu.et',
            'password' => 'SecurePass123!',
            'role_id' => $studentRole->id,
            'is_active' => true,
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'createdstudent@wu.edu.et']);

        // Now test login as that user via API
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'createdstudent@wu.edu.et',
            'password' => 'SecurePass123!',
        ]);

        $loginRes->assertStatus(200);
        $this->assertEquals('createdstudent@wu.edu.et', $loginRes->json('user.email'));
    }

    /**
     * BL-01 & BL-02: Item update ignores 'status' field (mass assignment bypass prevented)
     * and correctly updates actual schema columns (incident_date, color, brand, serial_number).
     */
    public function test_bl_01_and_bl_02_item_update_ignores_status_and_updates_valid_columns(): void
    {
        $user = User::factory()->student()->create(['is_active' => true]);
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Hall', 'code' => 'LOC-MH-01', 'zone' => 'hall']);
        $category = Category::create(['name' => 'Electronics']);

        $item = Item::create([
            'reference_code' => 'WU-TEST-BL01',
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Original Black Dell Laptop',
            'description' => 'Original detailed description for Dell laptop test with length over twenty chars',
            'incident_date' => '2026-09-01',
            'brand' => 'Dell',
            'color' => 'Black',
            'serial_number' => 'SN-1111',
        ]);

        $this->actingAs($user);

        // Attempt to pass 'status' => 'returned' (BL-01 attack) alongside valid updates (BL-02)
        $res = $this->putJson("/api/v1/items/{$item->id}", [
            'title' => 'Updated Dell Laptop Title',
            'description' => 'Updated detailed description for Dell laptop test with length over twenty chars',
            'status' => 'returned',
            'brand' => 'Dell XPS',
            'color' => 'Silver',
            'serial_number' => 'SN-9999',
            'incident_date' => '2026-09-10',
        ]);

        $res->assertStatus(200);

        $freshItem = $item->fresh();
        // BL-01: Status MUST remain 'lost', NOT updated to 'returned'
        $this->assertEquals('lost', $freshItem->status, 'BL-01: Item status must not be modified via update endpoint');
        // BL-02: Valid schema fields were properly updated
        $this->assertEquals('Dell XPS', $freshItem->brand);
        $this->assertEquals('Silver', $freshItem->color);
        $this->assertEquals('SN-9999', $freshItem->serial_number);
        $this->assertEquals('2026-09-10', $freshItem->incident_date->format('Y-m-d'));
    }

    /**
     * BL-03: DELETE /api/v1/items/{id} returns RFC 9110 compliant 204 No Content with empty body.
     */
    public function test_bl_03_item_destroy_returns_empty_204_no_content(): void
    {
        $user = User::factory()->student()->create(['is_active' => true]);
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Library', 'code' => 'LOC-LIB-02', 'zone' => 'library']);
        $category = Category::create(['name' => 'Books']);

        $item = Item::create([
            'reference_code' => 'WU-TEST-BL03',
            'reporter_id' => $user->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Withdrawable Item Book',
            'description' => 'Detailed description for textbook test with length over twenty chars',
            'incident_date' => '2026-09-01',
        ]);

        $this->actingAs($user);

        $res = $this->deleteJson("/api/v1/items/{$item->id}");

        $res->assertStatus(204);
        $res->assertNoContent();
        $this->assertSame('', $res->getContent(), 'BL-03: HTTP 204 response body must be completely empty per RFC 9110');
    }

    /**
     * BL-06: resolveCampusId returns 422 when no valid campus is supplied and user has no campus.
     */
    public function test_bl_06_item_creation_without_campus_or_location_returns_422(): void
    {
        $user = User::factory()->student()->create(['is_active' => true]);
        $category = Category::create(['name' => 'Bags']);

        $this->actingAs($user);

        // Omit campus_id and location_id
        $res = $this->postJson('/api/v1/items/lost', [
            'category_id' => $category->id,
            'title' => 'Lost Backpack Near Gate',
            'description' => 'Black backpack with university documents test length over twenty characters',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['campus_id']);
    }

    /**
     * RBAC-02 & RBAC-03: Guest gets 401, inactive user gets 403 on claims index and store.
     */
    public function test_rbac_02_and_03_claims_guest_and_inactive_user_access_blocked(): void
    {
        // 1. Guest -> 401
        $this->getJson('/api/v1/claims')->assertStatus(401);
        $this->postJson('/api/v1/claims', ['item_id' => 1])->assertStatus(401);

        // 2. Inactive user -> 403
        $inactiveUser = User::factory()->student()->create(['is_active' => false]);
        $this->actingAs($inactiveUser);

        $this->getJson('/api/v1/claims')->assertStatus(403);
        $this->postJson('/api/v1/claims', ['item_id' => 1])->assertStatus(403);
    }

    /**
     * Requirement 6: ClaimController index and show IDOR and row-level scoping.
     * Non-owner student cannot view another user's claim details (403),
     * and index only returns the claimant's own claims for students.
     */
    public function test_claim_controller_idor_and_row_level_scoping(): void
    {
        $studentA = User::factory()->student()->create(['is_active' => true]);
        $studentB = User::factory()->student()->create(['is_active' => true]);
        $staff = User::factory()->staff()->create(['is_active' => true]);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Gate', 'code' => 'LOC-MG-01', 'zone' => 'gate']);
        $category = Category::create(['name' => 'Phones']);

        $foundItemA = Item::create([
            'reference_code' => 'WU-TEST-CLM-A',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found iPhone 13',
            'description' => 'Blue iPhone 13 found at main gate security station with twenty chars length',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $foundItemB = Item::create([
            'reference_code' => 'WU-TEST-CLM-B',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Found Samsung Galaxy',
            'description' => 'Black Samsung Galaxy found at gate security station with twenty chars length',
            'incident_date' => now()->format('Y-m-d'),
        ]);

        $claimA = Claim::create([
            'item_id' => $foundItemA->id,
            'claimant_id' => $studentA->id,
            'explanation' => 'Student A ownership explanation with sufficient length over fifty chars.',
            'status' => 'pending',
        ]);

        $claimB = Claim::create([
            'item_id' => $foundItemB->id,
            'claimant_id' => $studentB->id,
            'explanation' => 'Student B ownership explanation with sufficient length over fifty chars.',
            'status' => 'pending',
        ]);

        // Student B attempts to access Student A's claim via GET /claims/{id} -> IDOR blocked (403)
        $this->actingAs($studentB);
        $resShow = $this->getJson("/api/v1/claims/{$claimA->id}");
        $this->assertEquals(403, $resShow->status(), 'IDOR violation: Student B must not be able to view Student A claim');

        // Student B calls index -> only sees Claim B, never Claim A
        $resIndexB = $this->getJson('/api/v1/claims');
        $resIndexB->assertOk();
        $idsB = collect($resIndexB->json('data'))->pluck('id')->all();
        $this->assertContains($claimB->id, $idsB);
        $this->assertNotContains($claimA->id, $idsB, 'Row-level scoping: Student B must not see Student A claim in listing');

        // Staff calls index -> sees both claims
        $this->actingAs($staff);
        $resIndexStaff = $this->getJson('/api/v1/claims');
        $resIndexStaff->assertOk();
        $idsStaff = collect($resIndexStaff->json('data'))->pluck('id')->all();
        $this->assertContains($claimA->id, $idsStaff);
        $this->assertContains($claimB->id, $idsStaff);
    }

    /**
     * Requirement 7: Permission cache invalidation on role permission update.
     * Revoking a permission takes effect immediately on the next request.
     */
    public function test_permission_cache_revocation_takes_effect_on_next_request(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $reviewClaimPerm = Permission::where('name', 'REVIEW_CLAIMS')->firstOrFail();
        $role = Role::create([
            'name' => 'temp_claim_reviewer',
            'display_name' => 'Temp Claim Reviewer',
            'display_name_am' => 'ጊዜያዊ ገምጋሚ',
            'is_active' => true,
        ]);
        $role->permissions()->sync([$reviewClaimPerm->id]);

        $officer = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        // First request: officer has REVIEW_CLAIMS
        $this->actingAs($officer);
        $meRes1 = $this->getJson('/api/v1/auth/me');
        $meRes1->assertOk();
        $this->assertContains('REVIEW_CLAIMS', $meRes1->json('user.permissions'));

        // Admin revokes REVIEW_CLAIMS from the role
        $this->actingAs($admin);
        $syncRes = $this->postJson("/api/v1/admin/roles/{$role->id}/permissions", [
            'permission_ids' => [],
        ]);
        $syncRes->assertOk();

        // Officer's immediate next request: REVIEW_CLAIMS is gone, cache was invalidated!
        $this->actingAs($officer->fresh());
        $meRes2 = $this->getJson('/api/v1/auth/me');
        $meRes2->assertOk();
        $this->assertNotContains('REVIEW_CLAIMS', $meRes2->json('user.permissions'), 'Permission cache must be cleared when role permissions change');
    }

    /**
     * Requirement 9: RBAC Matrix coverage across Guest, Student, Staff, Admin.
     */
    public function test_rbac_matrix_representative_endpoints_across_roles(): void
    {
        $guest = null;
        $student = User::factory()->student()->create(['is_active' => true]);
        $staff = User::factory()->staff()->create(['is_active' => true]);
        $admin = User::factory()->admin()->create(['is_active' => true]);

        // 1. Public route accessible to guest and all roles
        $this->getJson('/api/v1/public/items')->assertOk();
        $this->actingAs($student)->getJson('/api/v1/public/items')->assertOk();
        $this->actingAs($staff)->getJson('/api/v1/public/items')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/public/items')->assertOk();

        // 2. Student authenticated route
        $this->actingAs($student)->getJson('/api/v1/items')->assertOk();
        $this->actingAs($staff)->getJson('/api/v1/items')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/items')->assertOk();

        // 3. Staff route blocked for Student, accessible to Staff and Admin
        $this->actingAs($student)->getJson('/api/v1/custody')->assertStatus(403);
        $this->actingAs($staff)->getJson('/api/v1/custody')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/custody')->assertOk();

        // 4. Admin route blocked for Student and Staff, accessible to Admin
        $this->actingAs($student)->getJson('/api/v1/admin/users')->assertStatus(403);
        $this->actingAs($staff)->getJson('/api/v1/admin/users')->assertStatus(403);
        $this->actingAs($admin)->getJson('/api/v1/admin/users')->assertOk();
    }
}
