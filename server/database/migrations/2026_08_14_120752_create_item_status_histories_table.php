<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->foreignId('changed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('from_status', [
                'lost', 'found_unclaimed', 'claimed',
                'returned', 'withdrawn', 'closed', 'expired',
            ])->nullable();
            $table->enum('to_status', [
                'lost', 'found_unclaimed', 'claimed',
                'returned', 'withdrawn', 'closed', 'expired',
            ]);
            $table->enum('changed_by_role', ['admin', 'staff', 'student', 'system'])
                  ->nullable();
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('item_id', 'idx_ish_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_status_histories');
    }
};
