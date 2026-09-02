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

    public function test_unauthenticated_user_cannot_access_stream(): void
    {
        $response = $this->get('/api/v1/notifications/stream');
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_sse_stream(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/v1/notifications/stream');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }

    public function test_notification_creation_dispatches_realtime_broadcast(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\NotificationCreated::class]);

        $user = User::factory()->create();
        $service = app(\App\Domain\Notifications\Services\NotificationService::class);

        $service->send(new \App\Domain\Notifications\DTOs\NotificationData(
            userId: $user->id,
            type: 'test_event',
            payload: ['message' => 'Real-time test message']
        ));

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\NotificationCreated::class, function ($event) use ($user) {
            return $event->notification->user_id === $user->id &&
                   $event->broadcastOn()[0]->name === "private-users.{$user->id}";
        });
    }

    public function test_claim_decision_notification_respects_email_preference(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $claimant = User::factory()->create(['email' => 'student@wollo.edu.et']);
        \App\Models\NotificationPreference::create([
            'user_id' => $claimant->id,
            'email_on_claim_decided' => false,
        ]);

        $campus = \App\Models\Campus::firstOrCreate(
            ['short_code' => 'MC'],
            ['name' => 'Main Campus', 'city' => 'Dessie', 'region' => 'Amhara']
        );
        $category = \App\Models\Category::firstOrCreate(
            ['name' => 'Electronics'],
            ['name_am' => 'ኤሌክትሮኒክስ']
        );

        $item = \App\Models\Item::create([
            'reference_code' => 'WU-ELCT9999',
            'reporter_id' => $claimant->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'HP Laptop',
            'description' => 'HP Laptop found in Library',
            'incident_date' => now(),
        ]);

        $claim = \App\Models\Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'approved',
            'explanation' => 'My personal HP Laptop with serial tag',
        ]);

        $job = new \App\Jobs\SendClaimDecisionNotification($claim, 'approved');
        $job->handle(app(\App\Domain\Notifications\Services\NotificationService::class));

        // Database notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $claimant->id,
            'type' => 'claim_approved',
        ]);

        // Email was NOT sent because preference is disabled
        \Illuminate\Support\Facades\Mail::assertNotSent(\App\Mail\Claims\ClaimApprovedMail::class);
    }
}
