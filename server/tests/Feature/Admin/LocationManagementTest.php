<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_location(): void
    {
        $admin = User::factory()->admin()->create();
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/locations', [
            'campus_id' => $campus->id,
            'name' => 'Science Building Block A',
            'code' => 'LOC-SCI-A',
            'zone' => 'academic',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Science Building Block A');

        $this->assertDatabaseHas('locations', [
            'name' => 'Science Building Block A',
            'code' => 'LOC-SCI-A',
        ]);
    }
}
