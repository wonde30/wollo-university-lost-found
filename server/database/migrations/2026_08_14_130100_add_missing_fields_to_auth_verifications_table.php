<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_verifications', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('token');
            $table->timestamp('last_sent_at')->nullable()->after('attempts');
            $table->timestamp('verified_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('auth_verifications', function (Blueprint $table) {
            $table->dropColumn(['code', 'last_sent_at', 'verified_at']);
        });
    }
};
