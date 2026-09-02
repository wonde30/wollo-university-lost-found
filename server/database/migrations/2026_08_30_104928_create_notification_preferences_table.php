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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('email_on_report_submitted')->default(true);
            $table->boolean('email_on_match_found')->default(true);
            $table->boolean('email_on_claim_received')->default(true);
            $table->boolean('email_on_claim_decided')->default(true);
            $table->boolean('email_on_item_returned')->default(true);
            $table->boolean('email_on_expiry_warning')->default(true);
            $table->boolean('email_on_item_expired')->default(true);
            $table->boolean('email_on_system_announcements')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
