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
        Schema::create('university_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 191)->unique();
            $table->string('institution_name', 191);
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['domain', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_domains');
    }
};
