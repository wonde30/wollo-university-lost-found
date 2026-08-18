<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('email', 191)->index();
            $table->enum('type', [
                'email_verification',
                'password_reset',
                'admin_password_reset',
            ]);
            $table->string('token', 255); // bcrypt-hashed OTP
            $table->string('plain_code', 10)->nullable(); // 6-digit OTP stored temporarily
            $table->unsignedTinyInteger('attempts')->default(0); // resend attempts
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable(); // null = unused
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['email', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_verifications');
    }
};
