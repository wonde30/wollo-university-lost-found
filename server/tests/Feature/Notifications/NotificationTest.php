<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_notifications_and_unread_count(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['message' => 'New claim submitted.'],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'item_match',
            'data' => ['message' => 'Potential match found.'],
            'is_read' => true,
            'read_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['message' => 'New claim submitted.'],
            'is_read' => false,
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['message' => 'Notification 1.'],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'item_match',
            'data' => ['message' => 'Notification 2.'],
            'is_read' => false,
        ]);

        $response = $this->patchJson('/api/v1/notifications/read-all');

        $response->assertOk();
        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }
}
