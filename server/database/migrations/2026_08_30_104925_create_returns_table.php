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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->restrictOnDelete();
            $table->foreignId('returned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('handed_over_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('storage_location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->date('return_date');
            $table->time('return_time')->nullable();
            $table->text('condition_on_return')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('recipient_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmation_token', 100)->nullable()->unique();
            $table->timestamp('confirmation_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
