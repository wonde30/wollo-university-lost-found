<?php

namespace Tests\Feature\Auth;

use App\Models\Campus;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_details(): void
    {
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $dept = Department::create(['campus_id' => $campus->id, 'name' => 'Computer Science', 'short_code' => 'CS', 'type' => 'department']);

        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Abebe Bikila',
            'university_id' => 'WU/12345/14',
            'email' => 'abebe.bikila@wollo.edu.et',
            'password' => 'WolloSecure123!@#',
            'phone' => '+251911223344',
            'department_id' => $dept->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'email', 'full_name', 'role'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'abebe.bikila@wollo.edu.et',
            'university_id' => 'WU/12345/14',
            'role' => 'student',
        ]);
    }
}
