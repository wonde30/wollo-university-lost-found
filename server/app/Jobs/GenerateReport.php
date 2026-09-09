<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\SearchLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateReport implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Report $report)
    {}

    public function handle(): void
    {
        $this->report->update(['status' => 'processing']);

        try {
            $format = strtolower($this->report->format ?? 'csv');
            $filePath = 'reports/' . Str::uuid() . '.' . $format;
            $fullPath = Storage::disk('local')->path($filePath);

            // Ensure directory exists
            Storage::disk('local')->makeDirectory('reports');

            // Gather report data, headers and metadata
            $reportData = $this->prepareReportData();
            $headers = $reportData['headers'];
            $rows = $reportData['rows'];
            $title = $reportData['title'];
            $filterSummary = $reportData['filterSummary'];

            $fileSize = 0;

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('reports.report_pdf', [
                    'report' => $this->report->loadMissing('requester'),
                    'reportTitle' => $title,
                    'headers' => $headers,
                    'rows' => $rows,
                    'filterSummary' => $filterSummary,
                ]);

                $pdf->setPaper('a4', 'landscape');
                $pdf->setOption('isPhpEnabled', true);
                $pdf->setOption('isRemoteEnabled', true);

                $output = $pdf->output();
                Storage::disk('local')->put($filePath, $output);
                $fileSize = strlen($output);
            } else {
                // Default CSV generation
                $handle = fopen($fullPath, 'w');
                if ($handle === false) {
                    throw new \RuntimeException("Unable to open file for writing: {$fullPath}");
                }

                // Add UTF-8 BOM for Excel / universal CSV compatibility
                fputs($handle, "\xEF\xBB\xBF");

                fputcsv($handle, $headers);
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);

                $fileSize = (int) filesize($fullPath);
            }

            $this->report->update([
                'status' => 'ready',
                'file_path' => $filePath,
                'file_size_bytes' => $fileSize,
                'row_count' => count($rows),
                'ready_at' => now(),
                'expires_at' => now()->addDays(7), // FR-58: 7-day expiry
            ]);

        } catch (Throwable $e) {
            Log::error("Report generation failed for Report #{$this->report->id}: " . $e->getMessage(), [
                'report_id' => $this->report->id,
                'exception' => $e,
            ]);

            $this->report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @return array{headers: array<string>, rows: array<array<string|int|float|null>>, title: string, filterSummary: string}
     */
    protected function prepareReportData(): array
    {
        $rawFilters = $this->report->filters ?? [];
        // Flatten nested filters if present
        $filters = isset($rawFilters['filters']) && is_array($rawFilters['filters'])
            ? array_merge($rawFilters, $rawFilters['filters'])
            : $rawFilters;

        $type = $this->report->report_type;

        $filterParts = [];
        if (!empty($filters['date_from'])) {
            $filterParts[] = 'From: ' . $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $filterParts[] = 'To: ' . $filters['date_to'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $filterParts[] = 'Status: ' . ucfirst(str_replace('_', ' ', (string)$filters['status']));
        }
        if (!empty($filters['campus_id']) && $filters['campus_id'] !== 'all') {
            $filterParts[] = 'Campus ID: ' . $filters['campus_id'];
        }
        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $filterParts[] = 'Category ID: ' . $filters['category_id'];
        }

        $filterSummary = count($filterParts) > 0 ? implode(' | ', $filterParts) : 'All Records (No Filters)';

        if ($type === 'items' || $type === 'item_list') {
            $headers = ['ID', 'Reference Code', 'Title', 'Type', 'Status', 'Category', 'Campus', 'Location', 'Incident Date', 'Created At'];
            $query = Item::with(['category', 'campus', 'location'])->where('is_deleted', false);

            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
                $query->where('category_id', $filters['category_id']);
            }
            if (!empty($filters['campus_id']) && $filters['campus_id'] !== 'all') {
                $query->where('campus_id', $filters['campus_id']);
            }
            if (!empty($filters['date_from'])) {
                $query->where(function ($q) use ($filters) {
                    $q->whereDate('incident_date', '>=', $filters['date_from'])
                      ->orWhereDate('created_at', '>=', $filters['date_from']);
                });
            }
            if (!empty($filters['date_to'])) {
                $query->where(function ($q) use ($filters) {
                    $q->whereDate('incident_date', '<=', $filters['date_to'])
                      ->orWhereDate('created_at', '<=', $filters['date_to']);
                });
            }

            $rows = [];
            foreach ($query->orderByDesc('id')->get() as $item) {
                $rows[] = [
                    $item->id,
                    $item->reference_code,
                    $item->title,
                    ucfirst((string) $item->type),
                    (string) $item->status,
                    $item->category?->name ?? 'N/A',
                    $item->campus?->name ?? 'N/A',
                    $item->location?->name ?? 'N/A',
                    $item->incident_date?->format('Y-m-d') ?? 'N/A',
                    $item->created_at?->format('Y-m-d H:i') ?? 'N/A',
                ];
            }

            return [
                'title' => 'Item Inventory Report',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        if ($type === 'returns') {
            $headers = ['ID', 'Item Reference', 'Item Title', 'Recipient', 'Staff', 'Return Date', 'Condition', 'Recipient Confirmed'];
            $query = ReturnRecord::with(['item', 'recipient', 'staff'])->orderByDesc('id');

            if (!empty($filters['date_from'])) {
                $query->whereDate('return_date', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('return_date', '<=', $filters['date_to']);
            }

            $rows = [];
            foreach ($query->get() as $ret) {
                $rows[] = [
                    $ret->id,
                    $ret->item?->reference_code ?? 'N/A',
                    $ret->item?->title ?? 'N/A',
                    $ret->recipient?->full_name ?? 'N/A',
                    $ret->staff?->full_name ?? 'N/A',
                    $ret->return_date ? date('Y-m-d H:i', strtotime((string)$ret->return_date)) : 'N/A',
                    $ret->condition_on_return ?? 'Good',
                    $ret->recipient_confirmed ? 'Yes' : 'No',
                ];
            }

            return [
                'title' => 'Property Returns & Dispatches',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        if ($type === 'claim_summary') {
            $headers = ['Claim ID', 'Item Reference', 'Claimant Name', 'Status', 'Review Note', 'Submitted At', 'Reviewed At'];
            $query = Claim::with(['item', 'claimant'])->orderByDesc('id');

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            $rows = [];
            foreach ($query->get() as $claim) {
                $rows[] = [
                    $claim->id,
                    $claim->item?->reference_code ?? 'N/A',
                    $claim->claimant?->full_name ?? 'N/A',
                    (string) $claim->status,
                    $claim->review_note ?? 'None',
                    $claim->created_at?->format('Y-m-d H:i') ?? 'N/A',
                    $claim->reviewed_at?->format('Y-m-d H:i') ?? 'Pending',
                ];
            }

            return [
                'title' => 'Claims Summary & Verification Report',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        if ($type === 'user_activity') {
            $headers = ['User ID', 'Full Name', 'Email', 'Role', 'Status', 'Registered At', 'Items Reported', 'Claims Filed'];
            $users = User::withCount(['items', 'claims'])->orderBy('id')->get();
            $rows = [];
            foreach ($users as $user) {
                $rows[] = [
                    $user->id,
                    $user->full_name,
                    $user->email,
                    $user->getRoleName(),
                    $user->is_active ? 'Active' : 'Suspended',
                    $user->created_at?->format('Y-m-d H:i') ?? 'N/A',
                    $user->items_count,
                    $user->claims_count,
                ];
            }

            return [
                'title' => 'User Activity & Engagement Report',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        if ($type === 'resolution_time') {
            $headers = ['Item ID', 'Reference Code', 'Title', 'Category', 'Date Reported', 'Date Returned', 'Resolution Time (Days)'];
            $items = Item::with(['category'])->where('status', 'returned')->where('is_deleted', false)->orderByDesc('id')->get();
            $rows = [];
            foreach ($items as $item) {
                $resDays = $item->last_activity_at && $item->created_at
                    ? $item->created_at->diffInDays($item->last_activity_at)
                    : 'N/A';

                $rows[] = [
                    $item->id,
                    $item->reference_code,
                    $item->title,
                    $item->category?->name ?? 'N/A',
                    $item->created_at?->toDateString() ?? 'N/A',
                    $item->last_activity_at?->toDateString() ?? 'N/A',
                    $resDays,
                ];
            }

            return [
                'title' => 'Resolution Turnaround Analytics',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        if ($type === 'search_analytics') {
            $headers = ['Search ID', 'Query String', 'Results Count', 'Zero-Result Failure', 'User ID', 'IP Address', 'Timestamp'];
            $searches = SearchLog::latest()->take(1000)->get();
            $rows = [];
            foreach ($searches as $search) {
                $rows[] = [
                    $search->id,
                    $search->query ?? 'All Items',
                    $search->results_count,
                    $search->results_count === 0 ? 'Yes (Failure)' : 'No',
                    $search->user_id ?? 'Guest',
                    $search->ip_address ?? 'N/A',
                    $search->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            }

            return [
                'title' => 'Search Analytics & Discovery Failure Report',
                'headers' => $headers,
                'rows' => $rows,
                'filterSummary' => $filterSummary,
            ];
        }

        // Fallback generic audit log export
        $headers = ['ID', 'Event', 'Auditable Type', 'Auditable ID', 'User', 'IP Address', 'Timestamp'];
        $logs = AuditLog::with('user')->latest()->take(500)->get();
        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log->id,
                $log->action,
                $log->auditable_type ?? 'N/A',
                $log->auditable_id ?? 'N/A',
                $log->user?->full_name ?? 'System',
                $log->ip_address ?? 'N/A',
                $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        return [
            'title' => 'System Audit Logs Export',
            'headers' => $headers,
            'rows' => $rows,
            'filterSummary' => $filterSummary,
        ];
    }
}
