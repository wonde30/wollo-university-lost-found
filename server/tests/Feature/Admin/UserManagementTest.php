<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->student()->create();

        $this->actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/users/{$user->id}/role", [
            'role' => 'staff',
        ]);

        $response->assertOk();
        $this->assertEquals('staff', $user->fresh()->getRoleName());
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin);

        $response = $this->patchJson("/api/v1/admin/users/{$user->id}/toggle-active");

        $response->assertOk();
        $this->assertFalse((bool) $user->fresh()->is_active);
    }
}
