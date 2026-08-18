<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')
                  ->unique()
                  ->constrained('claims')
                  ->restrictOnDelete();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->restrictOnDelete();
            $table->foreignId('returned_to')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('handed_over_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('storage_location_id')
                  ->nullable()
                  ->constrained('storage_locations')
                  ->nullOnDelete();
            $table->date('return_date');
            $table->time('return_time')->nullable();
            $table->enum('condition_on_return', [
                'good',
                'damaged',
                'incomplete',
            ])->default('good');
            $table->text('notes')->nullable();
            $table->boolean('recipient_confirmed')->default(false); // student acknowledged receipt
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
