<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\SystemAnnouncement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_announcement(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/announcements', [
            'title' => 'System Maintenance Notice',
            'body' => 'The system will undergo maintenance on Sunday.',
            'type' => 'warning',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'System Maintenance Notice');

        $this->assertDatabaseHas('system_announcements', [
            'title' => 'System Maintenance Notice',
        ]);
    }
}
