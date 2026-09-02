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
        Schema::create('match_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('lost_item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->decimal('category_score', 5, 2)->nullable();
            $table->decimal('text_score', 5, 2)->nullable();
            $table->decimal('location_score', 5, 2)->nullable();
            $table->string('algorithm_version', 50)->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['found_item_id', 'lost_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_suggestions');
    }
};
