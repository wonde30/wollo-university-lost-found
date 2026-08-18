<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@wu.edu.et'], [
            'full_name' => 'System Administrator',
            'university_id' => 'ADMIN-001',
            'password' => Hash::make('Password@123'),
            'role' => 'admin',
            'phone' => '+251911000001',
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'staff@wu.edu.et'], [
            'full_name' => 'Campus staff',
            'university_id' => 'STAFF-001',
            'password' => Hash::make('Password@123'),
            'role' => 'staff',
            'phone' => '+251911000002',
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'student@wu.edu.et'], [
            'full_name' => 'Wollo Student',
            'university_id' => 'STUDENT-001',
            'password' => Hash::make('Password@123'),
            'role' => 'student',
            'phone' => '+251911000003',
            'is_active' => true,
        ]);
    }
}
