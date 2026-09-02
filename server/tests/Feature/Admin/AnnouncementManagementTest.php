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

    public function test_admin_can_list_and_search_announcements(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Important Examination Notice',
            'body' => 'Please bring your student ID card.',
            'type' => 'info',
            'audience' => 'students',
            'is_active' => true,
        ]);

        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Campus Power Maintenance',
            'body' => 'Power will be interrupted on Sunday.',
            'type' => 'warning',
            'audience' => 'all',
            'is_active' => false,
        ]);

        // List all
        $response = $this->getJson('/api/v1/admin/announcements');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Search by title/body
        $searchResponse = $this->getJson('/api/v1/admin/announcements?search=Examination');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Important Examination Notice');

        // Filter by type
        $filterResponse = $this->getJson('/api/v1/admin/announcements?type=warning');
        $filterResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'warning');

        // Filter by active status
        $activeResponse = $this->getJson('/api/v1/admin/announcements?is_active=1');
        $activeResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_announcement(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/announcements', [
            'title' => 'System Maintenance Notice',
            'body' => 'The system will undergo maintenance on Sunday.',
            'type' => 'warning',
            'audience' => 'all',
            'is_active' => true,
            'starts_at' => now()->toISOString(),
            'ends_at' => now()->addDays(7)->toISOString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'System Maintenance Notice')
            ->assertJsonPath('data.audience', 'all');

        $this->assertDatabaseHas('system_announcements', [
            'title' => 'System Maintenance Notice',
            'type' => 'warning',
            'audience' => 'all',
        ]);
    }

    public function test_admin_can_update_announcement(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $announcement = SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Old Title',
            'body' => 'Old Body',
            'type' => 'info',
            'audience' => 'students',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/admin/announcements/{$announcement->id}", [
            'title' => 'Updated Title',
            'body' => 'Updated Body Description',
            'type' => 'urgent',
            'audience' => 'all',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.type', 'urgent')
            ->assertJsonPath('data.audience', 'all');

        $this->assertDatabaseHas('system_announcements', [
            'id' => $announcement->id,
            'title' => 'Updated Title',
            'type' => 'urgent',
        ]);
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $announcement = SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Toggle Notice',
            'body' => 'Toggle Body',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => true,
        ]);

        $response = $this->patchJson("/api/v1/admin/announcements/{$announcement->id}/toggle-active");
        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('system_announcements', [
            'id' => $announcement->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $announcement = SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'To be deleted',
            'body' => 'Body text',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/admin/announcements/{$announcement->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('system_announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_non_admin_cannot_access_admin_announcements(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $response = $this->getJson('/api/v1/admin/announcements');
        $response->assertStatus(403);

        $postResponse = $this->postJson('/api/v1/admin/announcements', [
            'title' => 'Unauthorized Post',
            'body' => 'Text',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_public_and_student_can_fetch_active_audience_filtered_announcements(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. All audience active
        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Global Public Notice',
            'body' => 'Visible to all.',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => true,
        ]);

        // 2. Student-only active
        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Student Only Notice',
            'body' => 'Visible to students only.',
            'type' => 'warning',
            'audience' => 'students',
            'is_active' => true,
        ]);

        // 3. Staff-only active
        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Staff Protocol',
            'body' => 'Visible to staff only.',
            'type' => 'urgent',
            'audience' => 'staff',
            'is_active' => true,
        ]);

        // 4. Inactive notice (should never appear)
        SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Inactive Notice',
            'body' => 'Should not be visible.',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => false,
        ]);

        // Guest user checks public endpoint -> sees only 'all'
        $guestResponse = $this->getJson('/api/v1/public/announcements');
        $guestResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Global Public Notice');

        // Student user checks active endpoint -> sees 'all' and 'students' (2 total)
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $studentResponse = $this->getJson('/api/v1/announcements/active');
        $studentResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_bulk_toggle_and_bulk_delete_announcements(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $a1 = SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Bulk Item 1',
            'body' => 'Body 1',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => true,
        ]);
        $a2 = SystemAnnouncement::create([
            'created_by' => $admin->id,
            'title' => 'Bulk Item 2',
            'body' => 'Body 2',
            'type' => 'info',
            'audience' => 'all',
            'is_active' => true,
        ]);

        // Bulk deactivate
        $toggleRes = $this->postJson('/api/v1/admin/announcements/bulk-toggle', [
            'ids' => [$a1->id, $a2->id],
            'is_active' => false,
        ]);
        $toggleRes->assertStatus(200);

        $this->assertDatabaseHas('system_announcements', ['id' => $a1->id, 'is_active' => false]);
        $this->assertDatabaseHas('system_announcements', ['id' => $a2->id, 'is_active' => false]);

        // Bulk delete
        $deleteRes = $this->postJson('/api/v1/admin/announcements/bulk-delete', [
            'ids' => [$a1->id, $a2->id],
        ]);
        $deleteRes->assertStatus(200);

        $this->assertDatabaseMissing('system_announcements', ['id' => $a1->id]);
        $this->assertDatabaseMissing('system_announcements', ['id' => $a2->id]);
    }
}
