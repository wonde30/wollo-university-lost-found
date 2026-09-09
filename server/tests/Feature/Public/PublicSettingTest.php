<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SystemSettingSeeder::class);
    }

    public function test_can_fetch_public_settings_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/public/settings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'institution_name',
                'site_name',
                'theme_primary_color',
                'default_locale',
                'logo_url',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('Wollo University', $data['institution_name']);
        $this->assertArrayNotHasKey('otp_expiry_minutes', $data);
        $this->assertArrayNotHasKey('login_lockout_attempts', $data);
    }

    public function test_public_settings_are_cached_and_cast(): void
    {
        $response = $this->getJson('/api/v1/public/settings');
        $response->assertStatus(200);

        $this->assertIsString($response->json('data.institution_name'));
        $this->assertIsString($response->json('data.theme_primary_color'));
    }
}
