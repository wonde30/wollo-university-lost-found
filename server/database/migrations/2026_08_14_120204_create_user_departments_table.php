<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->restrictOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->year('enrolled_year')->nullable();
            $table->timestamps();

            // One primary department per user
            $table->unique(['user_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_departments');
    }
};
