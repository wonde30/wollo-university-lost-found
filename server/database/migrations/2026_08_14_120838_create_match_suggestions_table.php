<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->foreignId('lost_item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->decimal('score', 5, 2);    // DECIMAL(5,2) = 0.00 to 100.00
            $table->decimal('category_score', 5, 2)->default(0); // 0 or 50
            $table->decimal('text_score', 5, 2)->default(0);     // Jaccard 0-50
            $table->decimal('location_score', 5, 2)->default(0); // same campus bonus
            $table->enum('status', [
                'pending',   // not yet reviewed by system or staff
                'notified',  // reporter has been notified
                'confirmed', // staff confirmed it is the same item
                'dismissed', // staff dismissed — not the same item
            ])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['found_item_id', 'lost_item_id'], 'uq_match_pair');
            $table->index('score', 'idx_match_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_suggestions');
    }
};
