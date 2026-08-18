<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Issue 3 fix — remove the duplicate OTP column.
 *
 * The original base migration (2026_08_14_120306) created `plain_code(10)`.
 * The alter migration (2026_08_14_130100) added `code(10)` for the same purpose.
 * OtpService, VerificationController, and PasswordResetController all use `code`.
 * `plain_code` is dead weight — dropping it here prevents future confusion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_verifications', function (Blueprint $table) {
            $table->dropColumn('plain_code');
        });
    }

    public function down(): void
    {
        Schema::table('auth_verifications', function (Blueprint $table) {
            $table->string('plain_code', 10)->nullable()->after('token');
        });
    }
};
