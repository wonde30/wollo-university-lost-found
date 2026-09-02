<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_can_list_permission_groups(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/permission-groups');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name', 'is_system', 'is_active', 'permissions_count'],
                ],
                'meta',
            ]);
    }

    public function test_admin_can_create_custom_permission_group(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/permission-groups', [
                'name' => 'notifications_alerts',
                'display_name' => 'Notifications & Alerts',
                'display_name_am' => 'ማሳወቂያዎች እና ማስጠንቀቂያዎች',
                'description' => 'Permissions governing dispatch and configuration of push notifications.',
                'description_am' => 'የማሳወቂያዎች አሰራር እና መመሪያ ፈቃዶች።',
                'is_active' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'notifications_alerts')
            ->assertJsonPath('data.display_name', 'Notifications & Alerts')
            ->assertJsonPath('data.is_system', false);

        $this->assertDatabaseHas('permission_groups', [
            'name' => 'notifications_alerts',
            'display_name' => 'Notifications & Alerts',
        ]);
    }

    public function test_admin_can_update_permission_group(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::create([
            'name' => 'custom_operations',
            'display_name' => 'Custom Operations',
            'is_system' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/permission-groups/{$group->id}", [
                'display_name' => 'Updated Custom Operations',
                'display_name_am' => 'የተሻሻለ ብጁ ስራ',
                'description' => 'Updated description.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.display_name', 'Updated Custom Operations');

        $this->assertDatabaseHas('permission_groups', [
            'id' => $group->id,
            'display_name' => 'Updated Custom Operations',
        ]);
    }

    public function test_admin_can_toggle_permission_group_active(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::where('name', 'item_management')->firstOrFail();

        $this->assertTrue($group->is_active);

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/admin/permission-groups/{$group->id}/toggle-active");

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($group->fresh()->is_active);
    }

    public function test_admin_cannot_delete_system_permission_group(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::where('name', 'item_management')->firstOrFail();

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/permission-groups/{$group->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'System permission groups cannot be deleted.');

        $this->assertDatabaseHas('permission_groups', ['id' => $group->id]);
    }

    public function test_admin_cannot_delete_group_with_assigned_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::create([
            'name' => 'temp_group',
            'display_name' => 'Temporary Group',
            'is_system' => false,
            'is_active' => true,
        ]);

        Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'TEMP_PERM',
            'display_name' => 'Temp Perm',
            'category' => 'items',
            'is_system' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/permission-groups/{$group->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Cannot delete permission group with assigned permissions. Reassign or remove permissions first.');

        $this->assertDatabaseHas('permission_groups', ['id' => $group->id]);
    }

    public function test_admin_can_delete_empty_custom_permission_group(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::create([
            'name' => 'empty_custom_group',
            'display_name' => 'Empty Custom Group',
            'is_system' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/permission-groups/{$group->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Permission group deleted successfully.');

        $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
    }

    public function test_admin_can_create_permission_inside_a_group(): void
    {
        $admin = User::factory()->admin()->create();
        $group = PermissionGroup::where('name', 'custody_returns')->firstOrFail();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/permissions', [
                'permission_group_id' => $group->id,
                'name' => 'AUDIT_VAULT_INVENTORY',
                'display_name' => 'Audit Vault Inventory',
                'display_name_am' => 'የካዝና ቆጠራ ማካሄድ',
                'description' => 'Perform physical stock audits of campus property lockers.',
                'category' => 'custody',
                'is_active' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.permission_group_id', $group->id)
            ->assertJsonPath('data.name', 'AUDIT_VAULT_INVENTORY');

        $this->assertDatabaseHas('permissions', [
            'name' => 'AUDIT_VAULT_INVENTORY',
            'permission_group_id' => $group->id,
        ]);
    }

    public function test_admin_can_update_permission_group_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $perm = Permission::where('name', 'REPORT_LOST')->firstOrFail();
        $newGroup = PermissionGroup::where('name', 'claims_management')->firstOrFail();

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/permissions/{$perm->id}", [
                'permission_group_id' => $newGroup->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.permission_group_id', $newGroup->id);

        $this->assertEquals($newGroup->id, $perm->fresh()->permission_group_id);
    }

    public function test_staff_and_student_cannot_manage_permission_groups(): void
    {
        $staff = User::factory()->staff()->create();
        $student = User::factory()->create();

        $this->actingAs($staff)
            ->getJson('/api/v1/admin/permission-groups')
            ->assertStatus(403);

        $this->actingAs($staff)
            ->postJson('/api/v1/admin/permission-groups', ['display_name' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/permission-groups')
            ->assertStatus(403);

        $this->actingAs($student)
            ->postJson('/api/v1/admin/permission-groups', ['display_name' => 'Unauthorized'])
            ->assertStatus(403);
    }

    public function test_existing_role_permissions_remain_functional(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $student = User::factory()->create();

        $this->assertTrue($admin->hasPermission('REPORT_LOST'));
        $this->assertTrue($admin->hasPermission('MANAGE_USERS'));
        $this->assertTrue($admin->hasPermission('PROCESS_RETURNS'));

        $this->assertTrue($staff->hasPermission('REPORT_LOST'));
        $this->assertTrue($staff->hasPermission('PROCESS_RETURNS'));
        $this->assertFalse($staff->hasPermission('MANAGE_USERS'));

        $this->assertTrue($student->hasPermission('REPORT_LOST'));
        $this->assertFalse($student->hasPermission('PROCESS_RETURNS'));
        $this->assertFalse($student->hasPermission('MANAGE_USERS'));
    }
}
