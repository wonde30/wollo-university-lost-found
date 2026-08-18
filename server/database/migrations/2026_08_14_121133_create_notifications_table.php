<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('type', 80);     // e.g. App\Notifications\ClaimApproved
            $table->string('channel', 20)->default('database'); // database | email | sms
            $table->json('data');           // {title, message, action_url, reference_code}
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_read'], 'idx_notif_user_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
