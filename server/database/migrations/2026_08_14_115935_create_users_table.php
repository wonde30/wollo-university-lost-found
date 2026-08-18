<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('university_id', 30)->unique();
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->enum('role', ['admin', 'staff', 'student'])->default('student');
            $table->char('language', 2)->default('en');
            $table->boolean('is_active')->default(true);
            $table->string('profile_photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Seed default admin — change password immediately after first login
        DB::table('users')->insert([
            'full_name'         => 'System Administrator',
            'university_id'     => 'ADMIN-001',
            'email'             => 'admin@wu.edu.et',
            'email_verified_at' => now(),
            'password'          => Hash::make('Admin@Wollo2026!'),
            'role'              => 'admin',
            'language'          => 'en',
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('users')->insert([
            'full_name'         => 'Security Office — Dessie',
            'university_id'     => 'STAFF-DSS-001',
            'email'             => 'security.dessie@wu.edu.et',
            'email_verified_at' => now(),
            'password'          => Hash::make('Staff@Dessie2026!'),
            'role'              => 'staff',
            'language'          => 'en',
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('users')->insert([
            'full_name'         => 'Security Office — KIoT',
            'university_id'     => 'STAFF-KIT-001',
            'email'             => 'security.kiot@wu.edu.et',
            'email_verified_at' => now(),
            'password'          => Hash::make('Staff@KIoT2026!'),
            'role'              => 'staff',
            'language'          => 'en',
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
