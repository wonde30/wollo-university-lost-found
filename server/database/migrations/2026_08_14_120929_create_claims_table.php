<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->foreignId('claimant_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->text('explanation');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('auto_rejected')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'claimant_id'],   'uq_claims_user_item');
            $table->index(['item_id', 'status'],          'idx_claims_item_status');
            $table->index('claimant_id',                  'idx_claims_claimant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
