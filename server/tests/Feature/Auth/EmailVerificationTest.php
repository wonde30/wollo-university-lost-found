<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AuthVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_email_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $otp = '123456';
        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
            'code' => $otp,
            'token' => \Illuminate\Support\Facades\Hash::make($otp),
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => $otp,
        ]);

        $response->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_resend_verification_otp(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/resend-verification', [
            'email' => $user->email,
            'type' => 'email_verification',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('auth_verifications', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
        ]);
    }
}
