<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_department(): void
    {
        $admin = User::factory()->admin()->create();
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/departments', [
            'campus_id' => $campus->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Department of Computer Science');

        $this->assertDatabaseHas('departments', [
            'name' => 'Department of Computer Science',
            'short_code' => 'CS',
        ]);
    }
}
