<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->string('tag', 60);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['item_id', 'tag']);
            $table->index('tag', 'idx_tags_tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_tags');
    }
};
