<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_campus(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/campuses', [
            'name' => 'Komi Campus',
            'code' => 'KC',
            'city' => 'Kombolcha',
            'region' => 'Amhara',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Komi Campus');

        $this->assertDatabaseHas('campuses', [
            'name' => 'Komi Campus',
            'short_code' => 'KC',
        ]);
    }

    public function test_admin_can_list_campuses(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/campuses');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }
}
