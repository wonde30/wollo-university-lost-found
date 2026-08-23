<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_report_generation(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/reports/generate', [
            'report_type' => 'item_list',
            'format' => 'csv',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.report_type', 'item_list');

        $this->assertDatabaseHas('reports', [
            'report_type' => 'item_list',
        ]);
    }
}
