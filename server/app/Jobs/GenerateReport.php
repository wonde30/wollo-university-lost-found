<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Item;
use App\Models\ReturnRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateReport implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Report $report)
    {}

    public function handle(): void
    {
        $this->report->update(['status' => 'processing']);

        try {
            $format = $this->report->format ?? 'csv';
            $filePath = 'reports/' . Str::uuid() . '.' . $format;
            $fullPath = Storage::disk('local')->path($filePath);
            
            // Ensure directory exists
            Storage::disk('local')->makeDirectory('reports');

            $handle = fopen($fullPath, 'w');
            
            if ($this->report->report_type === 'items' || $this->report->report_type === 'item_list') {
                fputcsv($handle, ['ID', 'Reference Code', 'Title', 'Type', 'Status', 'Category', 'Campus', 'Location', 'Incident Date', 'Created At']);
                
                $query = Item::with(['category', 'campus', 'location'])->where('is_deleted', false);
                
                if (!empty($this->report->filters['status'])) {
                    $query->where('status', $this->report->filters['status']);
                }
                if (!empty($this->report->filters['category_id'])) {
                    $query->where('category_id', $this->report->filters['category_id']);
                }
                if (!empty($this->report->filters['campus_id'])) {
                    $query->where('campus_id', $this->report->filters['campus_id']);
                }
                if (!empty($this->report->filters['date_from'])) {
                    $query->where('incident_date', '>=', $this->report->filters['date_from']);
                }
                if (!empty($this->report->filters['date_to'])) {
                    $query->where('incident_date', '<=', $this->report->filters['date_to']);
                }
                
                foreach ($query->get() as $item) {
                    fputcsv($handle, [
                        $item->id,
                        $item->reference_code,
                        $item->title,
                        (string) $item->type,
                        (string) $item->status,
                        $item->category?->name ?? 'N/A',
                        $item->campus?->name ?? 'N/A',
                        $item->location?->name ?? 'N/A',
                        $item->incident_date?->format('Y-m-d') ?? 'N/A',
                        $item->created_at?->toIso8601String() ?? 'N/A',
                    ]);
                }
            } elseif ($this->report->report_type === 'returns') {
                fputcsv($handle, ['ID', 'Item Reference', 'Item Title', 'Recipient', 'Staff', 'Return Date', 'Condition', 'Confirmed By Recipient']);
                
                $returns = ReturnRecord::with(['item', 'recipient', 'staff'])->get();
                foreach ($returns as $ret) {
                    fputcsv($handle, [
                        $ret->id,
                        $ret->item?->reference_code ?? 'N/A',
                        $ret->item?->title ?? 'N/A',
                        $ret->recipient?->full_name ?? 'N/A',
                        $ret->staff?->full_name ?? 'N/A',
                        $ret->return_date,
                        $ret->condition_on_return,
                        $ret->recipient_confirmed ? 'Yes' : 'No',
                    ]);
                }
            } elseif ($this->report->report_type === 'claim_summary') {
                fputcsv($handle, ['Claim ID', 'Item Reference', 'Claimant Name', 'Status', 'Review Note', 'Submitted At', 'Reviewed At']);
                
                $claims = \App\Models\Claim::with(['item', 'claimant'])->get();
                foreach ($claims as $claim) {
                    fputcsv($handle, [
                        $claim->id,
                        $claim->item?->reference_code ?? 'N/A',
                        $claim->claimant?->full_name ?? 'N/A',
                        (string) $claim->status,
                        $claim->review_note ?? 'None',
                        $claim->created_at?->toIso8601String() ?? 'N/A',
                        $claim->reviewed_at?->toIso8601String() ?? 'Pending',
                    ]);
                }
            } elseif ($this->report->report_type === 'user_activity') {
                fputcsv($handle, ['User ID', 'Full Name', 'Email', 'Role', 'Status', 'Registered At', 'Items Reported', 'Claims Filed']);
                
                $users = \App\Models\User::withCount(['items', 'claims'])->get();
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->full_name,
                        $user->email,
                        $user->getRoleName(),
                        $user->is_active ? 'Active' : 'Suspended',
                        $user->created_at?->toIso8601String() ?? 'N/A',
                        $user->items_count,
                        $user->claims_count,
                    ]);
                }
            } elseif ($this->report->report_type === 'resolution_time') {
                fputcsv($handle, ['Item ID', 'Reference Code', 'Title', 'Category', 'Date Reported', 'Date Returned', 'Resolution Time (Days)']);
                
                $items = Item::with(['category'])->where('status', 'returned')->where('is_deleted', false)->get();
                foreach ($items as $item) {
                    $resDays = $item->last_activity_at && $item->created_at 
                        ? $item->created_at->diffInDays($item->last_activity_at) 
                        : 'N/A';
                    fputcsv($handle, [
                        $item->id,
                        $item->reference_code,
                        $item->title,
                        $item->category?->name ?? 'N/A',
                        $item->created_at?->toDateString() ?? 'N/A',
                        $item->last_activity_at?->toDateString() ?? 'N/A',
                        $resDays,
                    ]);
                }
            } elseif ($this->report->report_type === 'search_analytics') {
                fputcsv($handle, ['Search ID', 'Query String', 'Results Count', 'Zero-Result Failure', 'User ID', 'IP Address', 'Timestamp']);
                
                $searches = \App\Models\SearchLog::latest()->take(1000)->get();
                foreach ($searches as $search) {
                    fputcsv($handle, [
                        $search->id,
                        $search->query ?? 'All Items',
                        $search->results_count,
                        $search->results_count === 0 ? 'Yes (Failure)' : 'No',
                        $search->user_id ?? 'Guest',
                        $search->ip_address ?? 'N/A',
                        $search->created_at?->toIso8601String() ?? 'N/A',
                    ]);
                }
            } else {
                // Fallback generic audit log export
                fputcsv($handle, ['ID', 'Event', 'Auditable Type', 'Auditable ID', 'User', 'IP Address', 'Timestamp']);
                
                $logs = \App\Models\AuditLog::with('user')->latest()->take(500)->get();
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->action,
                        $log->auditable_type ?? 'N/A',
                        $log->auditable_id ?? 'N/A',
                        $log->user?->full_name ?? 'System',
                        $log->ip_address ?? 'N/A',
                        $log->created_at?->toIso8601String() ?? 'N/A',
                    ]);
                }
            }

            fclose($handle);

            $this->report->update([
                'status' => 'ready',
                'file_path' => $filePath,
                'ready_at' => now(),
                'expires_at' => now()->addDays(7), // FR-58: 7-day expiry
            ]);

        } catch (\Exception $e) {
            $this->report->update(['status' => 'failed']);
            throw $e;
        }
    }
}
