<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageLocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_storage_location(): void
    {
        $admin = User::factory()->admin()->create();
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/storage-locations', [
            'campus_id' => $campus->id,
            'name' => 'Secure Vault A',
            'code' => 'SL-VLT-A',
            'type' => 'safe',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Secure Vault A');

        $this->assertDatabaseHas('storage_locations', [
            'name' => 'Secure Vault A',
            'code' => 'SL-VLT-A',
        ]);
    }
}
