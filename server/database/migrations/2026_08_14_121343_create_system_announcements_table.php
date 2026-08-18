<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->string('title', 100);
            $table->string('body', 500);
            $table->enum('type', [
                'info',
                'warning',
                'maintenance',
                'success',
            ])->default('info');
            $table->enum('audience', [
                'all',
                'students',
                'staff',
                'admin',
            ])->default('all');
            $table->boolean('is_active')->default(false); // must be explicitly activated
            $table->timestamp('starts_at')->nullable();   // schedule display start
            $table->timestamp('ends_at')->nullable();     // auto-deactivate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_announcements');
    }
};
