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
        Schema::create('user_organizational_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organizational_unit_id')->constrained('organizational_units')->restrictOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('enrolled_year')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'organizational_unit_id'], 'uou_user_unit_unique');
            $table->index(['user_id', 'is_primary'], 'uou_user_primary_idx');
            $table->index(['organizational_unit_id', 'is_primary'], 'uou_unit_primary_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_organizational_units');
    }
};
