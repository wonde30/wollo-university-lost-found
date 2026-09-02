<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicRolePropagationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_dynamic_custom_role_creation_assignment_filtering_and_auth_me_propagation(): void
    {
        $admin = User::factory()->admin()->create([
            'is_active' => true,
        ]);

        $reviewClaimPerm = Permission::where('name', 'REVIEW_CLAIMS')->firstOrFail();
        $manageCustodyPerm = Permission::where('name', 'MANAGE_CUSTODY')->firstOrFail();

        // 1. Admin creates a custom role 'claims_officer' via API
        $createRoleRes = $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'name' => 'claims_officer',
            'display_name' => 'Claims Officer',
            'display_name_am' => 'የይገባኛል ጥያቄ መኮንን',
            'description' => 'Handles item claims operations',
            'is_active' => true,
            'permission_ids' => [$reviewClaimPerm->id, $manageCustodyPerm->id],
        ]);

        $createRoleRes->assertStatus(201);
        $roleId = $createRoleRes->json('data.id');
        $this->assertDatabaseHas('roles', ['name' => 'claims_officer']);

        // 2. Roles API index includes the new custom role
        $rolesListRes = $this->actingAs($admin)->getJson('/api/v1/admin/roles?all=true');
        $rolesListRes->assertOk();
        $roleNames = collect($rolesListRes->json('data'))->pluck('name')->all();
        $this->assertContains('claims_officer', $roleNames);

        // 3. Admin assigns custom role to an existing user via API
        $targetUser = User::factory()->student()->create([
            'is_active' => true,
        ]);

        $updateRoleRes = $this->actingAs($admin)->patchJson("/api/v1/admin/users/{$targetUser->id}/role", [
            'role' => 'claims_officer',
        ]);

        $updateRoleRes->assertOk();
        $this->assertEquals('claims_officer', $targetUser->fresh()->getRoleName());

        // 4. Admin Users list filters by custom role 'claims_officer'
        $usersFilterRes = $this->actingAs($admin)->getJson('/api/v1/admin/users?role=claims_officer');
        $usersFilterRes->assertOk();
        $filteredUserIds = collect($usersFilterRes->json('data'))->pluck('id')->all();
        $this->assertContains($targetUser->id, $filteredUserIds);

        // 5. User logs in / calls /auth/me -> Returns custom role name and permissions
        $authMeRes = $this->actingAs($targetUser->fresh())->getJson('/api/v1/auth/me');
        $authMeRes->assertOk();
        $this->assertEquals('claims_officer', $authMeRes->json('user.role'));
        $permissions = $authMeRes->json('user.permissions');
        $this->assertContains('REVIEW_CLAIMS', $permissions);
        $this->assertContains('MANAGE_CUSTODY', $permissions);

        // 6. Admin creates a second custom role 'audit_supervisor'
        $createRoleRes2 = $this->actingAs($admin)->postJson('/api/v1/admin/roles', [
            'name' => 'audit_supervisor',
            'display_name' => 'Audit Supervisor',
            'display_name_am' => 'የኦዲት ተቆጣጣሪ',
            'description' => 'Supervises compliance and audit logs',
            'is_active' => true,
            'permission_ids' => [$reviewClaimPerm->id],
        ]);

        $createRoleRes2->assertStatus(201);
        $this->assertDatabaseHas('roles', ['name' => 'audit_supervisor']);

        // 7. Both custom roles appear in the roles index dynamically
        $rolesListRes2 = $this->actingAs($admin)->getJson('/api/v1/admin/roles?all=true');
        $rolesListRes2->assertOk();
        $allNames = collect($rolesListRes2->json('data'))->pluck('name')->all();
        $this->assertContains('claims_officer', $allNames);
        $this->assertContains('audit_supervisor', $allNames);
    }
}
