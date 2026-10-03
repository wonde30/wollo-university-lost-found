<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AuthVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_email_with_valid_otp(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'student.verify@wu.edu.et',
            'email_verified_at' => null,
            'password' => null,
            'is_active' => false,
        ]);

        $otp = '123456';
        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
            'code' => $otp,
            'token' => Hash::make($otp),
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => $otp,
        ]);

        $response->assertOk();
        $refreshed = $user->fresh();
        $this->assertNotNull($refreshed->email_verified_at);
        $this->assertTrue($refreshed->is_active);
        $this->assertNotNull($refreshed->password);
    }

    public function test_user_can_resend_verification_otp(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'student.resend@wu.edu.et',
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
