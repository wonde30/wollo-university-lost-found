<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Jobs\GenerateReport;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_can_request_csv_report_generation(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/reports/generate', [
            'report_type' => 'item_list',
            'format' => 'csv',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.report_type', 'item_list')
            ->assertJsonPath('data.format', 'csv')
            ->assertJsonPath('data.status', 'queued');

        $this->assertDatabaseHas('reports', [
            'report_type' => 'item_list',
            'format' => 'csv',
        ]);
    }

    public function test_admin_can_generate_and_process_pdf_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $campus = Campus::factory()->create(['name' => 'Dessie Campus']);
        $category = Category::factory()->create(['name' => 'Electronics']);

        Item::factory()->count(3)->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'status' => 'reported',
            'type' => 'lost',
        ]);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'pdf',
            'status' => 'queued',
            'filters' => [
                'status' => 'reported',
                'campus_id' => $campus->id,
            ],
        ]);

        // Execute generation job synchronously
        $job = new GenerateReport($report);
        $job->handle();

        $report->refresh();

        $this->assertEquals('ready', $report->status);
        $this->assertNotNull($report->file_path);
        $this->assertGreaterThan(0, $report->file_size_bytes);
        $this->assertEquals(3, $report->row_count);
        $this->assertTrue(Storage::disk('local')->exists($report->file_path));

        // Verify valid PDF magic signature
        $pdfContent = Storage::disk('local')->get($report->file_path);
        $this->assertStringStartsWith('%PDF-', $pdfContent, 'Generated PDF must start with %PDF- header magic signature.');
        $this->assertStringContainsString('%%EOF', $pdfContent, 'Generated PDF must contain valid EOF marker.');
    }

    public function test_admin_can_generate_and_process_csv_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Item::factory()->count(2)->create([
            'status' => 'reported',
            'type' => 'found',
        ]);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'csv',
            'status' => 'queued',
        ]);

        $job = new GenerateReport($report);
        $job->handle();

        $report->refresh();

        $this->assertEquals('ready', $report->status);
        $this->assertTrue(Storage::disk('local')->exists($report->file_path));

        $csvContent = Storage::disk('local')->get($report->file_path);
        $this->assertStringContainsString('Reference Code', $csvContent);
        $this->assertGreaterThan(0, $report->file_size_bytes);
    }

    public function test_admin_can_download_pdf_report_with_correct_headers(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'pdf',
            'status' => 'queued',
        ]);

        $job = new GenerateReport($report);
        $job->handle();

        $report->refresh();

        $response = $this->get("/api/v1/admin/reports/{$report->id}/download");

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString("wollo_item_list_{$report->id}.pdf", $response->headers->get('Content-Disposition'));

        $report->refresh();
        $this->assertEquals(1, $report->download_count);
        $this->assertNotNull($report->downloaded_at);
    }

    public function test_admin_can_generate_claims_summary_pdf_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $claimant = User::factory()->student()->create();
        $item = Item::factory()->create();

        Claim::factory()->create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'status' => 'approved',
        ]);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'claim_summary',
            'format' => 'pdf',
            'status' => 'queued',
        ]);

        $job = new GenerateReport($report);
        $job->handle();

        $report->refresh();

        $this->assertEquals('ready', $report->status);
        $this->assertTrue(Storage::disk('local')->exists($report->file_path));
        $content = Storage::disk('local')->get($report->file_path);
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_student_cannot_generate_or_view_admin_reports(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $generateResponse = $this->postJson('/api/v1/admin/reports/generate', [
            'report_type' => 'item_list',
            'format' => 'pdf',
        ]);

        $generateResponse->assertStatus(403);

        $listResponse = $this->getJson('/api/v1/admin/reports');
        $listResponse->assertStatus(403);
    }

    public function test_download_fails_when_report_not_ready(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'pdf',
            'status' => 'queued',
        ]);

        $response = $this->getJson("/api/v1/admin/reports/{$report->id}/download");

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Report is not ready for download yet.');
    }

    public function test_download_fails_when_report_expired(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $report = Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'pdf',
            'status' => 'ready',
            'file_path' => 'reports/test.pdf',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson("/api/v1/admin/reports/{$report->id}/download");

        $response->assertStatus(410)
            ->assertJsonPath('message', 'This report has expired and is no longer available.');
    }

    public function test_admin_can_filter_reports_by_type_status_and_date_range(): void
    {
        $admin = User::factory()->admin()->create(['full_name' => 'Abebe Admin']);
        $this->actingAs($admin);

        // Report 1: matching filters
        Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'item_list',
            'format' => 'csv',
            'status' => 'ready',
            'file_path' => 'reports/test1.csv',
            'created_at' => now()->subDays(2),
        ]);

        // Report 2: different type
        Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'claim_summary',
            'format' => 'pdf',
            'status' => 'queued',
            'created_at' => now()->subDays(2),
        ]);

        // Filter by type 'item_list' and status 'ready'
        $response = $this->getJson('/api/v1/admin/reports?report_type=item_list&status=ready');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.report_type', 'item_list')
            ->assertJsonPath('data.0.status', 'ready')
            ->assertJsonPath('data.0.requester.full_name', 'Abebe Admin');
    }
}
