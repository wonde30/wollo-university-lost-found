<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')
                  ->constrained('claims')
                  ->cascadeOnDelete();
            $table->foreignId('changed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('from_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->enum('to_status', ['pending', 'approved', 'rejected']);
            $table->enum('changed_by_role', ['admin', 'staff', 'student', 'system'])->nullable();
            $table->boolean('was_auto_rejected')->default(false);
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('claim_id', 'idx_csh_claim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_status_histories');
    }
};
