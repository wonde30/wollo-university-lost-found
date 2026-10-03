<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\UniversityDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        UniversityDomain::flushDomainCache();
    }

    public function test_public_can_discover_active_university_domains(): void
    {
        UniversityDomain::create([
            'domain' => 'wu.edu.et',
            'institution_name' => 'Wollo University',
            'is_active' => true,
        ]);
        UniversityDomain::create([
            'domain' => 'inactive.edu.et',
            'institution_name' => 'Inactive University',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/public/university-domains');

        $response->assertOk()
            ->assertJsonFragment(['domain' => 'wu.edu.et', 'institution_name' => 'Wollo University'])
            ->assertJsonMissing(['domain' => 'inactive.edu.et']);
    }

    public function test_admin_can_list_and_manage_domains(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin', 'is_system' => true, 'is_active' => true]);
        $manageSettingsPerm = Permission::firstOrCreate(['name' => 'MANAGE_SETTINGS'], ['display_name' => 'Manage Settings', 'is_active' => true]);
        $adminRole->permissions()->syncWithoutDetaching([$manageSettingsPerm->id]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/v1/admin/university-domains', [
                'domain' => 'aau.edu.et',
                'institution_name' => 'Addis Ababa University',
                'is_active' => true,
                'description' => 'Test domain',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.domain', 'aau.edu.et');

        $domainId = $createResponse->json('data.id');

        // Toggle active
        $toggleResponse = $this->actingAs($admin)
            ->patchJson("/api/v1/admin/university-domains/{$domainId}/toggle-active");
        $toggleResponse->assertOk()
            ->assertJsonPath('data.is_active', false);

        // Update
        $updateResponse = $this->actingAs($admin)
            ->putJson("/api/v1/admin/university-domains/{$domainId}", [
                'institution_name' => 'AAU Updated',
            ]);
        $updateResponse->assertOk()
            ->assertJsonPath('data.institution_name', 'AAU Updated');

        // Delete
        $deleteResponse = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/university-domains/{$domainId}");
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('university_domains', ['id' => $domainId]);
    }

    public function test_student_cannot_manage_domains(): void
    {
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]);
        $student = User::factory()->create([
            'role_id' => $studentRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)
            ->postJson('/api/v1/admin/university-domains', [
                'domain' => 'hacked.edu.et',
                'institution_name' => 'Hacked',
                'is_active' => true,
            ]);

        $response->assertStatus(403);
    }

    public function test_duplicate_domain_creation_is_rejected(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin', 'is_system' => true, 'is_active' => true]);
        $manageSettingsPerm = Permission::firstOrCreate(['name' => 'MANAGE_SETTINGS'], ['display_name' => 'Manage Settings', 'is_active' => true]);
        $adminRole->permissions()->syncWithoutDetaching([$manageSettingsPerm->id]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        UniversityDomain::create([
            'domain' => 'unique.edu.et',
            'institution_name' => 'Unique University',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/university-domains', [
                'domain' => 'unique.edu.et',
                'institution_name' => 'Duplicate Attempt',
                'is_active' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }
}
