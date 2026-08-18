<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAndPermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_fr09_student_role_is_default_and_only_admin_can_change_role(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        $this->assertEquals(UserRole::STUDENT, $student->role);

        // Student cannot change own or other's role (FR-13 / FR-09)
        Sanctum::actingAs($student);
        $this->patchJson("/api/v1/admin/users/{$student->id}/role", ['role' => 'admin'])->assertStatus(403);

        // Admin can change user role (FR-09, FR-12)
        Sanctum::actingAs($admin);
        $this->patchJson("/api/v1/admin/users/{$student->id}/role", ['role' => 'staff'])->assertOk();
        $this->assertEquals(UserRole::STAFF, $student->fresh()->role);
    }

    public function test_fr09_role_snapshot_stored_in_audit_logs_at_event_time(): void
    {
        $staff = User::factory()->staff()->create();
        Sanctum::actingAs($staff);

        AuditLogger::log('item.status_changed', null, ['status' => 'lost'], ['status' => 'found_unclaimed'], $staff);

        $log = AuditLog::where('actor_id', $staff->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('staff', $log->actor_role);
    }

    public function test_fr10_student_cannot_access_staff_or_admin_routes(): void
    {
        $student = User::factory()->create();
        Sanctum::actingAs($student);

        $this->getJson('/api/v1/custody')->assertStatus(403);
        $this->getJson('/api/v1/returns')->assertStatus(403);
        $this->getJson('/api/v1/admin/dashboard/statistics')->assertStatus(403);
        $this->getJson('/api/v1/admin/audit-logs')->assertStatus(403);
    }

    public function test_fr10_student_cannot_see_reporter_contact_details_without_approved_claim(): void
    {
        $reporter = User::factory()->create([
            'full_name' => 'Original Reporter',
            'email' => 'reporter@wollo.edu.et',
            'phone' => '+251911223344',
        ]);
        $otherStudent = User::factory()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Library', 'code' => 'LOC-LIB-02', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics'], ['name_am' => 'ኤሌክትሮኒክስ']);

        $item = Item::create([
            'reference_code' => 'WU-LAP01234',
            'reporter_id' => $reporter->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Lost MacBook Air 13 inch',
            'description' => 'Grey MacBook Air M2 chip lost in second floor study cubicle',
            'incident_date' => now(),
        ]);

        Sanctum::actingAs($otherStudent);
        $response = $this->getJson("/api/v1/items/{$item->id}");
        $response->assertOk();

        // Contact info must not be leaked to general students
        $response->assertJsonPath('data.reporter.name', 'Original Reporter');
        $response->assertJsonMissingPath('data.reporter.email');
        $response->assertJsonMissingPath('data.reporter.phone');
    }

    public function test_fr11_staff_can_review_claims_and_manage_custody(): void
    {
        $staff = User::factory()->staff()->create();
        $claimant = User::factory()->create();

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Hall', 'code' => 'LOC-HALL-01', 'zone' => 'administrative']);
        $category = Category::create(['name' => 'Bags', 'name_am' => 'ቦርሳ']);

        $item = Item::create([
            'reference_code' => 'WU-BAG01234',
            'reporter_id' => $staff->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Blue Backpack with Notebooks',
            'description' => 'Found in main hallway containing 3 notebooks and blue pencil case',
            'incident_date' => now(),
        ]);

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'pending',
            'explanation' => 'My blue backpack which contains my 3 computer science lecture notebooks',
        ]);

        Sanctum::actingAs($staff);

        $response = $this->postJson("/api/v1/claims/{$claim->id}/review", [
            'status' => 'approved',
            'review_note' => 'Ownership verified with notebook student name match',
        ]);
        $response->assertOk();
        $this->assertEquals('approved', $claim->fresh()->status->value);
        $this->assertEquals('claimed', $item->fresh()->status->value);
    }

    public function test_fr12_admin_can_access_audit_logs_and_export_csv(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        AuditLogger::log('user.created', $admin, null, ['name' => 'New User'], $admin);

        $response = $this->getJson('/api/v1/admin/audit-logs');
        $response->assertOk()->assertJsonStructure(['data', 'meta']);

        $csvResponse = $this->get('/api/v1/admin/audit-logs/export');
        $csvResponse->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
