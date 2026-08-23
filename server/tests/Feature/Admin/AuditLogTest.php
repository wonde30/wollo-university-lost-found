<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->admin()->create();
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'user.login',
            'auditable_type' => User::class,
            'auditable_id' => $admin->id,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/audit-logs');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }
}
