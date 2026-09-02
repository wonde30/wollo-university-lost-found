<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAndPermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_can_list_roles(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/roles');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name', 'is_system', 'is_active', 'permissions'],
                ],
                'meta',
            ]);
    }

    public function test_admin_can_list_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/permissions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name', 'category', 'is_system', 'is_active'],
                ],
                'meta',
            ]);
    }

    public function test_admin_can_create_custom_role_with_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $perms = Permission::take(3)->pluck('id')->all();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/roles', [
                'name' => 'campus_auditor',
                'display_name' => 'Campus Auditor',
                'display_name_am' => 'የግቢ ኦዲተር',
                'description' => 'Auditing role for inventory reviews.',
                'is_active' => true,
                'permission_ids' => $perms,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'campus_auditor')
            ->assertJsonPath('data.display_name', 'Campus Auditor')
            ->assertJsonPath('data.is_system', false);

        $this->assertDatabaseHas('roles', ['name' => 'campus_auditor']);
    }

    public function test_admin_can_sync_role_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $customRole = Role::create([
            'name' => 'desk_assistant',
            'display_name' => 'Desk Assistant',
            'is_system' => false,
            'is_active' => true,
        ]);

        $perms = Permission::take(2)->pluck('id')->all();

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/roles/{$customRole->id}/permissions", [
                'permission_ids' => $perms,
            ]);

        $response->assertOk();
        $this->assertCount(2, $customRole->fresh()->permissions);
    }

    public function test_admin_cannot_delete_system_role(): void
    {
        $admin = User::factory()->admin()->create();
        $systemRole = Role::where('name', 'student')->firstOrFail();

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/roles/{$systemRole->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'System roles cannot be deleted.');

        $this->assertDatabaseHas('roles', ['name' => 'student']);
    }

    public function test_admin_can_create_custom_permission(): void
    {
        $admin = User::factory()->admin()->create();
        $group = \App\Models\PermissionGroup::firstOrFail();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/permissions', [
                'permission_group_id' => $group->id,
                'name' => 'EXPORT_CUSTOM_AUDITS',
                'display_name' => 'Export Custom Audits',
                'display_name_am' => 'ብጁ ኦዲቶችን መላክ',
                'description' => 'Export specialized audit reports.',
                'category' => 'admin',
                'is_active' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'EXPORT_CUSTOM_AUDITS')
            ->assertJsonPath('data.category', 'admin')
            ->assertJsonPath('data.permission_group_id', $group->id);

        $this->assertDatabaseHas('permissions', ['name' => 'EXPORT_CUSTOM_AUDITS', 'permission_group_id' => $group->id]);
    }

    public function test_admin_can_toggle_permission_active(): void
    {
        $admin = User::factory()->admin()->create();
        $perm = Permission::where('name', 'REPORT_LOST')->firstOrFail();

        $this->assertTrue($perm->is_active);

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/admin/permissions/{$perm->id}/toggle-active");

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($perm->fresh()->is_active);
    }

    public function test_staff_and_student_cannot_access_roles_or_permissions_management(): void
    {
        $staff = User::factory()->staff()->create();
        $student = User::factory()->create();

        $this->actingAs($staff)
            ->getJson('/api/v1/admin/roles')
            ->assertStatus(403);

        $this->actingAs($staff)
            ->getJson('/api/v1/admin/permissions')
            ->assertStatus(403);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/roles')
            ->assertStatus(403);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/permissions')
            ->assertStatus(403);
    }
}
