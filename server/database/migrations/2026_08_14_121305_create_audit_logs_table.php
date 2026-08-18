<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('actor_role', ['admin', 'staff', 'student', 'system'])->nullable();
            $table->string('action', 80);            // e.g. item.status_changed
            $table->string('auditable_type', 100);   // e.g. App\Models\Item
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('session_id', 255)->nullable(); // link audit to session
            // No updated_at — append-only table
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at',                         'idx_audit_created');
            $table->index('actor_id',                           'idx_audit_actor');
            $table->index(['auditable_type', 'auditable_id'],   'idx_audit_morph');
            $table->index('action',                             'idx_audit_action');
        });

        // Production only: restrict app DB user to SELECT + INSERT on this table
        // DB::statement("REVOKE UPDATE, DELETE ON wollo_lost_found.audit_logs FROM 'wollo_app'@'localhost'");
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
