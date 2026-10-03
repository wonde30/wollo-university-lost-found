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

    public function test_cross_user_idor_protection_prevents_marking_other_users_notification(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $notificationA = Notification::create([
            'user_id' => $userA->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 1, 'item_id' => 2],
            'is_read' => false,
        ]);

        // Acting as user B attempting to modify user A's notification
        $this->actingAs($userB);
        $response = $this->patchJson("/api/v1/notifications/{$notificationA->id}/read");

        $response->assertNotFound();
        $this->assertDatabaseHas('notifications', [
            'id' => $notificationA->id,
            'is_read' => false,
        ]);
    }

    public function test_notification_resource_includes_resolved_action_url(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 10, 'item_id' => 20, 'sub_type' => 'claimant'],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'report_generated',
            'data' => ['report_id' => 5],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'item_returned',
            'data' => ['confirmation_token' => 'secure-token-123'],
            'is_read' => false,
        ]);

        $response = $this->getJson('/api/v1/notifications');
        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(3, $data);

        $actionUrls = array_column($data, 'action_url');
        $this->assertContains('/student/my-claims', $actionUrls);
        $this->assertContains('/admin/reports', $actionUrls);
        $this->assertContains('/confirm-return/secure-token-123', $actionUrls);
    }

    public function test_generate_report_creates_report_generated_notification_on_completion(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $admin = User::factory()->create();
        $report = \App\Models\Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'inventory',
            'format' => 'csv',
            'status' => 'pending',
            'filters' => [],
        ]);

        $job = new \App\Jobs\GenerateReport($report);
        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'report_generated',
        ]);

        $notif = Notification::where('user_id', $admin->id)->where('type', 'report_generated')->first();
        $this->assertNotNull($notif);
        $this->assertEquals($report->id, $notif->data['report_id']);
        $this->assertEquals('inventory', $notif->data['report_type']);
    }

    public function test_claim_submitted_job_stores_structured_payload_without_hardcoded_message(): void
    {
        $claimant = User::factory()->create();
        $campus = \App\Models\Campus::firstOrCreate(
            ['short_code' => 'MC2'],
            ['name' => 'Main Campus 2', 'city' => 'Dessie', 'region' => 'Amhara']
        );
        $category = \App\Models\Category::firstOrCreate(
            ['name' => 'Books'],
            ['name_am' => 'መጻሕፍት']
        );

        $item = \App\Models\Item::create([
            'reference_code' => 'WU-BK0001',
            'reporter_id' => $claimant->id,
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'title' => 'Calculus Textbook',
            'description' => 'Math book',
            'incident_date' => now(),
        ]);

        $claim = \App\Models\Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'submitted',
            'explanation' => 'Left it in room 102',
        ]);

        $job = new \App\Jobs\SendClaimSubmittedNotification($claim);
        $job->handle(app(\App\Domain\Notifications\Services\NotificationService::class));

        $notif = Notification::where('user_id', $claimant->id)->where('type', 'claim_submitted')->first();
        $this->assertNotNull($notif);
        $this->assertEquals('claimant', $notif->data['sub_type']);
        $this->assertEquals($claim->id, $notif->data['claim_id']);
        $this->assertEquals($item->id, $notif->data['item_id']);
        $this->assertArrayNotHasKey('message', $notif->data);
    }

    public function test_user_can_get_unread_count(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 1],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 2],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'item_match',
            'data' => ['match_id' => 1],
            'is_read' => true,
            'read_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/notifications/unread-count');
        $response->assertOk()
            ->assertJsonPath('unread_count', 2);
    }

    public function test_user_can_mark_read_and_read_all_via_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 10],
            'is_read' => false,
        ]);

        // POST /api/v1/notifications/{id}/read
        $response = $this->postJson("/api/v1/notifications/{$notification->id}/read");
        $response->assertOk();
        $this->assertTrue($notification->fresh()->is_read);

        // Create another unread
        Notification::create([
            'user_id' => $user->id,
            'type' => 'report_generated',
            'data' => ['report_id' => 1],
            'is_read' => false,
        ]);

        // POST /api/v1/notifications/read-all
        $responseAll = $this->postJson('/api/v1/notifications/read-all');
        $responseAll->assertOk();
        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_user_can_delete_own_notification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 1],
            'is_read' => false,
        ]);

        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");
        $response->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_delete_other_users_notification(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $notificationA = Notification::create([
            'user_id' => $userA->id,
            'type' => 'claim_submitted',
            'data' => ['claim_id' => 1],
            'is_read' => false,
        ]);

        $this->actingAs($userB);
        $response = $this->deleteJson("/api/v1/notifications/{$notificationA->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $notificationA->id]);
    }
}
