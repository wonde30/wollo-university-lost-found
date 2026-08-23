<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AuthVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_otp(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('auth_verifications', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
        ]);
    }

    public function test_user_can_verify_password_reset_otp(): void
    {
        $user = User::factory()->create();
        $code = '654321';

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
            'code' => $code,
            'token' => 'test-reset-token-xyz',
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-password-reset', [
            'email' => $user->email,
            'code' => $code,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $user = User::factory()->create();
        $code = '654321';

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
            'code' => $code,
            'token' => 'test-reset-token-xyz',
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'otp' => $code,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
    }
}
