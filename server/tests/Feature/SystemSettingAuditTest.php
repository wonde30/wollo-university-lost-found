<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemSettingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CampusSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    public function test_system_setting_get_and_cache_invalidation(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'test_threshold'],
            ['value' => '45', 'type' => 'integer', 'is_public' => true]
        );

        $this->assertEquals(45, SystemSetting::get('test_threshold'));

        // Update value in database
        $setting = SystemSetting::where('key', 'test_threshold')->first();
        $setting->update(['value' => '80']);

        // Cache was automatically flushed via booted event
        $this->assertEquals(80, SystemSetting::get('test_threshold'));
    }

    public function test_login_lockout_attempts_uses_dynamic_system_setting(): void
    {
        // Configure lockout to trigger after only 2 attempts
        SystemSetting::updateOrCreate(
            ['key' => 'login_lockout_attempts'],
            ['value' => '2', 'type' => 'integer']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'login_lockout_minutes'],
            ['value' => '15', 'type' => 'integer']
        );

        $user = User::factory()->create([
            'email' => 'student.lockout@wollo.edu.et',
            'password' => Hash::make('ValidPassword123!'),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Attempt 1 with wrong password -> not locked yet
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword1',
        ])->assertStatus(422);

        $this->assertEquals(1, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->locked_until);

        // Attempt 2 with wrong password -> MUST trigger lockout based on dynamic threshold (2)
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword2',
        ])->assertStatus(422);

        $this->assertEquals(2, $user->fresh()->failed_login_attempts);
        $this->assertNotNull($user->fresh()->locked_until);
        $this->assertTrue($user->fresh()->locked_until->isFuture());

        // Subsequent attempt blocked with 429
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'ValidPassword123!',
        ])->assertStatus(429);
    }

    public function test_admin_can_update_system_settings_via_api(): void
    {
        $admin = User::factory()->admin()->create([
            'is_active' => true,
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'item_expiry_days'],
            ['value' => '90', 'type' => 'integer', 'is_public' => false]
        );

        $response = $this->actingAs($admin)->putJson('/api/v1/admin/settings/item_expiry_days', [
            'value' => '120',
        ]);

        $response->assertOk();
        $this->assertEquals(120, SystemSetting::get('item_expiry_days'));
    }
}
