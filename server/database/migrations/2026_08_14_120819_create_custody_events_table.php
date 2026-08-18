<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custody_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->foreignId('actor_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('storage_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            $table->enum('event_type', [
                'deposited',    // finder hands item to Security Office
                'transferred',  // moved to different storage location
                'inspected',    // staff checked condition
                'released',     // handed to claimant
                'disposed',     // unclaimed, disposed after expiry
            ]);
            $table->enum('condition', [
                'good',
                'damaged',
                'incomplete',
                'unknown',
            ])->default('unknown');
            $table->text('notes')->nullable();
            $table->string('reference_photo', 255)->nullable(); // photo of item condition at intake
            $table->timestamp('created_at')->useCurrent();

            $table->index(['item_id', 'event_type'], 'idx_custody_item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custody_events');
    }
};
