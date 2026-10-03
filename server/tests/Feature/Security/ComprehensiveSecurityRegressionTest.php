<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ComprehensiveSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private User $staff;
    private User $admin;
    private Item $itemOfUserB;
    private Claim $claimOfUserB;
    private Notification $notificationOfUserB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->userA = User::factory()->create(['email' => 'usera@wu.edu.et']);
        $this->userB = User::factory()->create(['email' => 'userb@wu.edu.et']);
        $this->staff = User::factory()->staff()->create(['email' => 'staff1@wu.edu.et']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin1@wu.edu.et']);

        $campus = Campus::first() ?? Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::first() ?? Location::create(['campus_id' => $campus->id, 'name' => 'Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::first() ?? Category::create(['name' => 'Electronics', 'display_name' => 'Electronics', 'icon' => 'laptop']);

        $this->itemOfUserB = Item::create([
            'reference_code' => 'WU-TEST-SEC01',
            'reporter_id' => $this->userB->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'User B Secure Item',
            'description' => 'Security test item belonging to User B with sufficient length for validation',
            'incident_date' => now(),
        ]);

        $this->claimOfUserB = Claim::create([
            'item_id' => $this->itemOfUserB->id,
            'claimant_id' => $this->userB->id,
            'explanation' => 'Detailed proof of ownership provided by User B for test item with more than fifty chars length.',
            'status' => 'pending',
        ]);

        $this->notificationOfUserB = Notification::create([
            'user_id' => $this->userB->id,
            'type' => 'item_matched',
            'channel' => 'database',
            'data' => ['title' => 'Secret User B Notification'],
            'is_read' => false,
        ]);
    }

    /**
     * 1. USER A -> USER B notification IDOR
     */
    public function test_user_a_cannot_access_or_modify_user_b_notification(): void
    {
        $this->actingAs($this->userA);

        // Cannot mark User B's notification read
        $res = $this->postJson("/api/v1/notifications/{$this->notificationOfUserB->id}/read");
        $this->assertTrue(in_array($res->status(), [403, 404]), "Expected 403/404, got {$res->status()}");

        // Cannot delete User B's notification
        $resDelete = $this->deleteJson("/api/v1/notifications/{$this->notificationOfUserB->id}");
        $this->assertTrue(in_array($resDelete->status(), [403, 404]), "Expected 403/404, got {$resDelete->status()}");
    }

    /**
     * 2. USER A -> USER B item unauthorized update/delete
     */
    public function test_user_a_cannot_update_or_delete_user_b_item(): void
    {
        $this->actingAs($this->userA);

        // Cannot update User B's item
        $resUpdate = $this->putJson("/api/v1/items/{$this->itemOfUserB->id}", [
            'title' => 'Tampered Title by User A',
        ]);
        $this->assertTrue(in_array($resUpdate->status(), [403, 404]), "Expected 403/404, got {$resUpdate->status()}");

        // Cannot delete User B's item
        $resDelete = $this->deleteJson("/api/v1/items/{$this->itemOfUserB->id}");
        $this->assertTrue(in_array($resDelete->status(), [403, 404]), "Expected 403/404, got {$resDelete->status()}");
    }

    /**
     * 3. USER A -> USER B claim unauthorized access
     */
    public function test_user_a_cannot_view_or_modify_user_b_claim(): void
    {
        $this->actingAs($this->userA);

        // Cannot view User B's private claim details
        $res = $this->getJson("/api/v1/claims/{$this->claimOfUserB->id}");
        $this->assertTrue(in_array($res->status(), [403, 404]), "Expected 403/404, got {$res->status()}");

        // Cannot review claim (review gate)
        $resReview = $this->postJson("/api/v1/claims/{$this->claimOfUserB->id}/review", [
            'status' => 'approved',
            'review_note' => 'Illegitimate review attempt',
        ]);
        $this->assertEquals(403, $resReview->status());
    }

    /**
     * 4. USER A -> admin endpoint (403)
     */
    public function test_user_a_cannot_access_admin_endpoints(): void
    {
        $this->actingAs($this->userA);

        $this->getJson('/api/v1/admin/dashboard/statistics')->assertStatus(403);
        $this->getJson('/api/v1/admin/audit-logs')->assertStatus(403);
        $this->getJson('/api/v1/admin/users')->assertStatus(403);
        $this->getJson('/api/v1/admin/settings')->assertStatus(403);
    }

    /**
     * 5. STUDENT -> staff route (403)
     */
    public function test_student_cannot_access_staff_routes(): void
    {
        $this->actingAs($this->userA);

        $this->getJson('/api/v1/custody')->assertStatus(403);
        $this->getJson('/api/v1/returns')->assertStatus(403);
        $this->getJson('/api/v1/match-suggestions')->assertStatus(403);
    }

    /**
     * 6. STAFF -> admin-only operation (403)
     */
    public function test_staff_cannot_perform_admin_only_operations(): void
    {
        $this->actingAs($this->staff);

        // Staff cannot elevate user roles
        $this->patchJson("/api/v1/admin/users/{$this->userA->id}/role", [
            'role' => 'admin',
        ])->assertStatus(403);

        // Staff cannot modify institutional settings
        $this->putJson('/api/v1/admin/settings/site_name', [
            'value' => 'Hacked Name',
        ])->assertStatus(403);

        // Staff cannot access admin audit logs
        $this->getJson('/api/v1/admin/audit-logs')->assertStatus(403);
    }

    /**
     * 7. Unauthenticated protected endpoint (401)
     */
    public function test_unauthenticated_access_to_protected_endpoints_is_blocked(): void
    {
        $this->getJson('/api/v1/items/1')->assertStatus(401);
        $this->postJson('/api/v1/claims', [])->assertStatus(401);
        $this->getJson('/api/v1/notifications')->assertStatus(401);
    }

    /**
     * 8. Malformed request handling (422)
     */
    public function test_malformed_request_returns_validation_error(): void
    {
        $this->actingAs($this->userA);

        $res = $this->postJson('/api/v1/items/lost', [
            'title' => '', // Required
            'category_id' => 'invalid-id-string',
        ]);
        $res->assertStatus(422);
    }

    /**
     * 9. Oversized upload (FR-35 max 5MB / 10MB)
     */
    public function test_oversized_file_upload_is_rejected(): void
    {
        $this->actingAs($this->userA);

        // 12MB file exceeds 5MB limit
        $oversizedFile = UploadedFile::fake()->create('huge-document.pdf', 12000, 'application/pdf');

        $res = $this->postJson('/api/v1/claims', [
            'item_id' => $this->itemOfUserB->id,
            'explanation' => 'Valid explanation with sufficient length over fifty characters for testing.',
            'evidence' => [$oversizedFile],
        ]);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['evidence.0']);
    }

    /**
     * 10. Unsupported upload type
     */
    public function test_unsupported_file_extension_is_rejected(): void
    {
        $this->actingAs($this->userA);

        $executableFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $res = $this->postJson('/api/v1/claims', [
            'item_id' => $this->itemOfUserB->id,
            'explanation' => 'Valid explanation with sufficient length over fifty characters for testing.',
            'evidence' => [$executableFile],
        ]);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['evidence.0']);
    }

    /**
     * 11. Privilege escalation attempt during registration or profile update
     */
    public function test_privilege_escalation_attempt_is_prevented(): void
    {
        // Attempt to pass role=admin during self-registration
        $res = $this->postJson('/api/v1/auth/register', [
            'name' => 'Attacker User',
            'email' => 'attacker@wu.edu.et',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'admin', // Malicious field injection
        ]);

        if ($res->status() === 201 || $res->status() === 200) {
            $createdUser = User::where('email', 'attacker@wu.edu.et')->first();
            $this->assertNotNull($createdUser);
            $this->assertEquals('student', $createdUser->getRoleName(), 'User must have default student role, not admin');
        } else {
            $this->assertTrue(in_array($res->status(), [400, 422]));
        }
    }
}
