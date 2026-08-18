<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->char('reference_code', 11)->unique(); // WU-XXXXXXXX — set in Item::boot()
            $table->foreignId('reporter_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('campus_id')
                  ->constrained('campuses')
                  ->restrictOnDelete();
            $table->enum('type', ['lost', 'found']);
            $table->string('title', 150);
            $table->text('description');
            $table->unsignedTinyInteger('category_id');
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->restrictOnDelete();
            $table->foreignId('location_id')
                  ->nullable()
                  ->constrained('locations')
                  ->nullOnDelete();
            $table->string('location_detail', 255)->nullable();
            $table->string('brand', 80)->nullable();   // e.g. Samsung, Tecno, Nike
            $table->string('color', 60)->nullable();   // e.g. Black, Blue, Red
            $table->string('serial_number', 100)->nullable(); // for electronics
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->enum('status', [
                'lost',
                'found_unclaimed',
                'claimed',
                'returned',
                'withdrawn',
                'closed',
                'expired',
            ])->default('lost');
            $table->enum('held_at', [
                'security_office',
                'with_finder',
                'unknown',
            ])->nullable();
            $table->decimal('estimated_value', 10, 2)->nullable(); // ETB — helps prioritise high-value items
            $table->boolean('is_high_value')->default(false); // phones, laptops, wallets flagged
            $table->boolean('is_deleted')->default(false);
            $table->foreignId('deleted_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // pre-computed: last_activity_at + 90 days
            $table->timestamps();

            $table->index(['status', 'is_deleted'],    'idx_items_status_deleted');
            $table->index('reporter_id',               'idx_items_reporter');
            $table->index('category_id',               'idx_items_category');
            $table->index('location_id',               'idx_items_location');
            $table->index('campus_id',                 'idx_items_campus');
            $table->index('last_activity_at',          'idx_items_activity');
            $table->index('expires_at',                'idx_items_expires');
            $table->index('is_high_value',             'idx_items_high_value');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE items ADD FULLTEXT ft_items_search (title, description)'
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE items DROP INDEX ft_items_search');
        }
        Schema::dropIfExists('items');
    }
};
