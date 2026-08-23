<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_retrieve_system_settings(): void
    {
        $admin = User::factory()->admin()->create();
        SystemSetting::create(['key' => 'retention_period_days', 'value' => '90', 'type' => 'integer']);

        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/settings');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_admin_can_update_system_setting(): void
    {
        $admin = User::factory()->admin()->create();
        SystemSetting::create(['key' => 'retention_period_days', 'value' => '90', 'type' => 'integer']);

        $this->actingAs($admin);

        $response = $this->putJson('/api/v1/admin/settings/retention_period_days', [
            'value' => '120',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('system_settings', [
            'key' => 'retention_period_days',
            'value' => '120',
        ]);
    }
}
