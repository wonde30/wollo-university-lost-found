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
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->foreignId('type_id')->constrained('organizational_unit_types')->restrictOnDelete();
            $table->string('name');
            $table->string('name_am')->nullable();
            $table->string('short_code', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['campus_id', 'short_code']);
            $table->unique(['campus_id', 'name']);
            $table->index(['campus_id', 'type_id']);
            $table->index(['parent_id', 'type_id']);
            $table->index(['parent_id', 'is_active']);
            $table->index(['campus_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizational_units');
    }
};
