<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'student@wollo.edu.et',
            'password' => 'Password123!@#',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@wollo.edu.et',
            'password' => 'Password123!@#',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'email', 'full_name', 'role'],
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'student@wollo.edu.et',
            'password' => 'Password123!@#',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@wollo.edu.et',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(422);
    }
}
