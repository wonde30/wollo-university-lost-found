<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->enum('report_type', [
                'item_list',
                'resolution_time',
                'user_activity',
                'claim_summary',
                'audit_export',
                'search_analytics',
            ]);
            $table->json('filters');            // date range, campus, category, status etc.
            $table->enum('format', ['csv', 'pdf'])->default('csv');
            $table->enum('status', [
                'queued',
                'processing',
                'ready',
                'failed',
            ])->default('queued');
            $table->string('file_path', 255)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('row_count')->nullable(); // how many records exported
            $table->text('error_message')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('downloaded_at')->nullable(); // first download timestamp
            $table->unsignedTinyInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // file auto-deleted after 7 days
            $table->timestamps();

            $table->index('requested_by',  'idx_reports_user');
            $table->index('status',        'idx_reports_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
