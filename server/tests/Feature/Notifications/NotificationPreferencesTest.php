<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_default_notification_preferences(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/notifications/preferences');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'email_on_report_submitted',
                    'email_on_match_found',
                    'email_on_claim_received',
                    'email_on_claim_decided',
                    'email_on_item_returned',
                    'email_on_expiry_warning',
                    'email_on_item_expired',
                    'email_on_system_announcements',
                ],
            ]);
    }

    public function test_user_can_update_notification_preferences(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson('/api/v1/notifications/preferences', [
            'email_on_report_submitted' => false,
            'email_on_match_found' => true,
            'email_on_claim_received' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email_on_report_submitted', false)
            ->assertJsonPath('data.email_on_match_found', true)
            ->assertJsonPath('data.email_on_claim_received', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'email_on_report_submitted' => false,
            'email_on_match_found' => true,
            'email_on_claim_received' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_preferences(): void
    {
        $response = $this->getJson('/api/v1/notifications/preferences');
        $response->assertUnauthorized();
    }
}
