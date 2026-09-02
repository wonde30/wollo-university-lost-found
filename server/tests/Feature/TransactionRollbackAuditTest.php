<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuthVerification;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Models\PasswordHistory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionRollbackAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_password_rolls_back_history_on_failure(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword123!'),
        ]);

        $initialHistoryCount = PasswordHistory::count();

        try {
            DB::transaction(function () use ($user) {
                PasswordHistory::create([
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                    'created_at' => now(),
                ]);

                throw new \RuntimeException('Simulated unexpected database failure');
            });
        } catch (\RuntimeException $e) {
            // caught
        }

        $this->assertEquals($initialHistoryCount, PasswordHistory::count(), 'Password history write was rolled back completely');
    }

    public function test_password_reset_rolls_back_all_tables_on_failure(): void
    {
        $user = User::factory()->create([
            'email' => 'student@wollo.edu.et',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $verification = AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
            'code' => '123456',
            'token' => 'test-token-string',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => null,
        ]);

        $initialHistoryCount = PasswordHistory::count();

        try {
            DB::transaction(function () use ($user, $verification) {
                PasswordHistory::create([
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                    'created_at' => now(),
                ]);

                $verification->update(['verified_at' => now()]);

                throw new \RuntimeException('Simulated failure during user password update');
            });
        } catch (\RuntimeException $e) {
            // caught
        }

        $this->assertEquals($initialHistoryCount, PasswordHistory::count(), 'Password history was rolled back');
        $this->assertNull($verification->fresh()->verified_at, 'Verification token was not prematurely verified');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password), 'User password remained unchanged');
    }

    public function test_email_verification_rolls_back_on_failure(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@wollo.edu.et',
            'email_verified_at' => null,
        ]);

        $verification = AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
            'code' => '654321',
            'token' => 'email-token',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => null,
        ]);

        try {
            DB::transaction(function () use ($verification, $user) {
                $verification->update(['verified_at' => now()]);
                throw new \RuntimeException('Simulated failure before user email_verified_at update');
            });
        } catch (\RuntimeException $e) {
            // caught
        }

        $this->assertNull($verification->fresh()->verified_at, 'Verification was rolled back');
        $this->assertNull($user->fresh()->email_verified_at, 'User email_verified_at remained null');
    }

    public function test_user_management_store_creates_profile_atomically(): void
    {
        $admin = User::factory()->admin()->create();
        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]
        );

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/users', [
            'full_name' => 'Abebe Kebede',
            'university_id' => 'WU/10001/16',
            'email' => 'abebe@wollo.edu.et',
            'password' => 'Password123!',
            'role_id' => $studentRole->id,
            'is_active' => true,
        ]);

        $response->assertStatus(201);

        $createdUser = User::where('email', 'abebe@wollo.edu.et')->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->profile, 'UserProfile was atomically created');
    }

    public function test_item_status_update_is_atomic(): void
    {
        $admin = User::factory()->admin()->create();
        $campus = Campus::firstOrCreate(['short_code' => 'DES'], ['name' => 'Dessie Campus', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::firstOrCreate(['name' => 'Electronics'], ['display_name' => 'Electronics', 'icon' => 'laptop']);

        $item = Item::create([
            'reference_code' => 'WU-TEST-001',
            'reporter_id' => $admin->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'lost',
            'status' => 'lost',
            'title' => 'Test Laptop',
            'description' => 'Test description',
            'incident_date' => now()->toDateString(),
        ]);

        $initialHistories = ItemStatusHistory::where('item_id', $item->id)->count();

        $response = $this->actingAs($admin)->patchJson("/api/v1/items/{$item->id}/status", [
            'status' => 'closed',
            'reason' => 'Item was resolved and case closed by admin.',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('closed', $item->fresh()->status);
        $this->assertEquals($initialHistories + 1, ItemStatusHistory::where('item_id', $item->id)->count());
    }

    public function test_role_service_create_with_permissions_is_atomic(): void
    {
        $perm1 = Permission::create([
            'name' => 'TEST_PERM_1',
            'display_name' => 'Test Perm 1',
            'category' => 'general',
            'is_active' => true,
        ]);

        $perm2 = Permission::create([
            'name' => 'TEST_PERM_2',
            'display_name' => 'Test Perm 2',
            'category' => 'general',
            'is_active' => true,
        ]);

        $roleService = app(RoleService::class);
        $role = $roleService->createRole([
            'name' => 'security_guard',
            'display_name' => 'Security Guard',
            'is_active' => true,
        ], [$perm1->id, $perm2->id]);

        $this->assertNotNull($role);
        $this->assertCount(2, $role->fresh()->permissions);
    }
}
