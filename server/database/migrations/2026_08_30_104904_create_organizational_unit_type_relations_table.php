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
        Schema::create('organizational_unit_type_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_type_id')->constrained('organizational_unit_types')->cascadeOnDelete();
            $table->foreignId('parent_type_id')->constrained('organizational_unit_types')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['child_type_id', 'parent_type_id'], 'out_rel_child_parent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizational_unit_type_relations');
    }
};
