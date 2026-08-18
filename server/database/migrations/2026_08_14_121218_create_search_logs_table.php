<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('query', 255);
            $table->unsignedTinyInteger('category_id')->nullable();
            $table->foreignId('campus_id')
                  ->nullable()
                  ->constrained('campuses')
                  ->nullOnDelete();
            $table->unsignedSmallInteger('results_count')->default(0);
            $table->boolean('found_match')->default(false); // did user click a result
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('query',      'idx_sl_query');
            $table->index('created_at', 'idx_sl_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
