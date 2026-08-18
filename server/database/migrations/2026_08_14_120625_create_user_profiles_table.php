<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('id_card_photo')->nullable(); // Security Office sees this during claim verification
            $table->unsignedTinyInteger('year_of_study')->nullable(); // 1–6 for students
            $table->enum('gender', ['male', 'female', 'prefer_not_to_say'])->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('home_town', 100)->nullable(); // Many WU students are from rural areas
            $table->string('bio', 300)->nullable();
            $table->json('notification_channels')->nullable(); // ['email', 'sms'] preferences
            $table->timestamp('last_seen_at')->nullable(); // Last login timestamp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
