<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Items browse & filtering composite index
        Schema::table('items', function (Blueprint $table) {
            $table->index(['type', 'status', 'is_deleted', 'created_at'], 'items_browse_idx');
        });

        // 2. Real-time SSE incremental notifications index
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'id'], 'notif_user_id_idx');
        });

        // 3. Claims claimant and status index
        Schema::table('claims', function (Blueprint $table) {
            $table->index(['claimant_id', 'status'], 'claims_claimant_status_idx');
            $table->index(['status', 'created_at'], 'claims_status_created_idx');
        });

        // 4. Audit logs actor and timestamp index
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['actor_id', 'created_at'], 'audit_actor_created_idx');
            $table->index(['action', 'created_at'], 'audit_action_created_idx');
        });

        // 5. Returns date and confirmation status index
        Schema::table('returns', function (Blueprint $table) {
            $table->index(['return_date', 'recipient_confirmed'], 'returns_date_confirm_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_browse_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_user_id_idx');
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex('claims_claimant_status_idx');
            $table->dropIndex('claims_status_created_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_actor_created_idx');
            $table->dropIndex('audit_action_created_idx');
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->dropIndex('returns_date_confirm_idx');
        });
    }
};
