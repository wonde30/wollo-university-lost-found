<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicAuthRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
        RateLimiter::clear('register');
        RateLimiter::clear('password-reset');
        RateLimiter::clear('otp-verify');
        RateLimiter::clear('otp-resend');

        $this->user = User::factory()->create([
            'email' => 'target.user@wu.edu.et',
            'password' => bcrypt('ValidPassword123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 1. Login: 5 attempts/min per user+IP, exact threshold rejection, 429 structure, retry_after.
     */
    public function test_login_rate_limiting_per_user_ip_enforces_five_attempts(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'target.user@wu.edu.et',
                'password' => 'WrongPassword!',
            ]);
            $this->assertNotEquals(429, $response->status(), "Request #{$i} should be allowed");
        }

        // 6th attempt must be rejected with 429
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'target.user@wu.edu.et',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure([
            'success',
            'message',
            'retry_after',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));
        $this->assertLessThanOrEqual(60, $response->json('retry_after'));
    }

    /**
     * 2. Login user+IP isolation: User A throttled does not block User B from same IP.
     */
    public function test_login_user_ip_isolation_allows_different_user_same_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'target.user@wu.edu.et',
                'password' => 'WrongPassword!',
            ]);
        }

        // User A is now throttled
        $this->postJson('/api/v1/auth/login', [
            'email' => 'target.user@wu.edu.et',
            'password' => 'WrongPassword!',
        ])->assertStatus(429);

        // User B from same IP is still allowed
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'second.user@wu.edu.et',
            'password' => 'WrongPassword!',
        ]);

        $this->assertNotEquals(429, $response->status());
    }

    /**
     * 3. Login global IP limit: 10 attempts/min per IP overall across distinct users.
     */
    public function test_login_global_ip_limit_blocks_after_ten_attempts(): void
    {
        // Send 10 attempts from distinct emails from the same IP (127.0.0.1)
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => "unique.student{$i}@wu.edu.et",
                'password' => 'WrongPassword!',
            ]);
            $this->assertNotEquals(429, $response->status(), "Attempt #{$i} should be allowed under IP limit of 10");
        }

        // 11th attempt from same IP (even with a brand new email) must be rejected with 429
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'eleventh.student@wu.edu.et',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Too many attempts from this network', $response->json('message'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Different IP is isolated and still allowed
        $responseOtherIp = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.150'])
            ->postJson('/api/v1/auth/login', [
                'email' => 'eleventh.student@wu.edu.et',
                'password' => 'WrongPassword!',
            ]);

        $this->assertNotEquals(429, $responseOtherIp->status());
    }

    /**
     * 4. Register: 5 attempts/min per IP, exact threshold, 429 structure, IP isolation.
     */
    public function test_register_rate_limiting_enforces_five_per_ip_and_isolates(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/v1/auth/register', [
                'full_name' => "Student $i",
                'email' => "student$i@wu.edu.et",
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ]);
            $this->assertNotEquals(429, $response->status(), "Register #{$i} should be allowed");
        }

        // 6th attempt must be 429
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => "Student 6",
            'email' => "student6@wu.edu.et",
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['success', 'message', 'retry_after']);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Different IP is isolated and allowed
        $responseOtherIp = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.88'])
            ->postJson('/api/v1/auth/register', [
                'full_name' => 'Student Isolated',
                'email' => 'student_isolated@wu.edu.et',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ]);

        $this->assertNotEquals(429, $responseOtherIp->status());
    }

    /**
     * 5. Forgot Password: 3 requests/min per email+IP, threshold rejection, 429 structure, isolation.
     */
    public function test_forgot_password_rate_limiting_and_isolation(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->postJson('/api/v1/auth/forgot-password', [
                'email' => 'target.user@wu.edu.et',
            ]);
            $this->assertNotEquals(429, $response->status(), "Forgot password #{$i} should be allowed");
        }

        // 4th attempt rejected
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'target.user@wu.edu.et',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['success', 'message', 'retry_after']);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Email isolation: separate email from same IP is allowed
        $responseOtherEmail = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'other.user@wu.edu.et',
        ]);
        $this->assertNotEquals(429, $responseOtherEmail->status());

        // IP isolation: same email from different IP is allowed
        $responseOtherIp = $this->withServerVariables(['REMOTE_ADDR' => '192.168.2.55'])
            ->postJson('/api/v1/auth/forgot-password', [
                'email' => 'target.user@wu.edu.et',
            ]);
        $this->assertNotEquals(429, $responseOtherIp->status());
    }

    /**
     * 6. Verify Email: 10 attempts/min per email+IP, 429 structure, isolation.
     */
    public function test_verify_email_rate_limiting_and_isolation(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->postJson('/api/v1/auth/verify-email', [
                'email' => 'target.user@wu.edu.et',
                'code' => '999999',
            ]);
            $this->assertNotEquals(429, $response->status(), "Verify email #{$i} should be allowed");
        }

        // 11th attempt rejected
        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'target.user@wu.edu.et',
            'code' => '999999',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['success', 'message', 'retry_after']);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Email isolation
        $responseOtherEmail = $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'other.user@wu.edu.et',
            'code' => '999999',
        ]);
        $this->assertNotEquals(429, $responseOtherEmail->status());
    }

    /**
     * 7. Verify Password Reset: 10 attempts/min per email+IP, 429 structure, isolation.
     */
    public function test_verify_password_reset_rate_limiting_and_isolation(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->postJson('/api/v1/auth/verify-password-reset', [
                'email' => 'target.user@wu.edu.et',
                'otp' => '999999',
            ]);
            $this->assertNotEquals(429, $response->status(), "Verify password reset #{$i} should be allowed");
        }

        // 11th attempt rejected
        $response = $this->postJson('/api/v1/auth/verify-password-reset', [
            'email' => 'target.user@wu.edu.et',
            'otp' => '999999',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['success', 'message', 'retry_after']);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Email isolation
        $responseOtherEmail = $this->postJson('/api/v1/auth/verify-password-reset', [
            'email' => 'other.user@wu.edu.et',
            'otp' => '999999',
        ]);
        $this->assertNotEquals(429, $responseOtherEmail->status());
    }

    /**
     * 8. Resend Verification: 2 requests/min per email+IP, 429 structure, isolation.
     */
    public function test_otp_resend_rate_limiting_and_isolation(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $response = $this->postJson('/api/v1/auth/resend-verification', [
                'email' => 'unknown.student@wu.edu.et',
            ]);
            $this->assertNotEquals(429, $response->status(), "Resend #{$i} should be allowed");
        }

        // 3rd attempt rejected
        $response = $this->postJson('/api/v1/auth/resend-verification', [
            'email' => 'unknown.student@wu.edu.et',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['success', 'message', 'retry_after']);
        $this->assertFalse($response->json('success'));
        $this->assertGreaterThan(0, $response->json('retry_after'));

        // Email isolation
        $responseOtherEmail = $this->postJson('/api/v1/auth/resend-verification', [
            'email' => 'other.student@wu.edu.et',
        ]);
        $this->assertNotEquals(429, $responseOtherEmail->status());
    }

    /**
     * 9. Decay/Window Behavior: Rate limit decays after 60 seconds.
     */
    public function test_rate_limit_decays_after_window_expires(): void
    {
        Carbon::setTestNow(now());

        // Exhaust forgot password limit (3 attempts)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', [
                'email' => 'target.user@wu.edu.et',
            ]);
        }

        // 4th is 429
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'target.user@wu.edu.et',
        ])->assertStatus(429);

        // Advance time by 61 seconds
        Carbon::setTestNow(now()->addSeconds(61));

        // Request must now be allowed again
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'target.user@wu.edu.et',
        ]);

        $this->assertNotEquals(429, $response->status(), 'Request should be allowed after rate limit window decays');
    }
}
