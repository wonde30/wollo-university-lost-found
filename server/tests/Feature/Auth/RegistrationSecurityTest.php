<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Jobs\SendRegistrationOtp;
use App\Jobs\SendTemporaryCredential;
use App\Models\AuthVerification;
use App\Models\PasswordHistory;
use App\Models\Role;
use App\Models\UniversityDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        UniversityDomain::flushDomainCache();

        UniversityDomain::create([
            'domain' => 'wu.edu.et',
            'institution_name' => 'Wollo University',
            'is_active' => true,
        ]);

        UniversityDomain::create([
            'domain' => 'inactive.edu.et',
            'institution_name' => 'Inactive Uni',
            'is_active' => false,
        ]);
    }

    public function test_active_university_domain_accepted(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Kassahun Belay',
            'university_id' => 'WU/998877/14',
            'email' => 'kassahun@wu.edu.et',
            'phone' => '+251911000111',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'kassahun@wu.edu.et')
            ->assertJsonMissing(['password', 'otp', 'token']);

        $user = User::where('email', 'kassahun@wu.edu.et')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active);
        $this->assertNull($user->password);
        $this->assertNull($user->email_verified_at);

        Queue::assertPushed(SendRegistrationOtp::class);
    }

    public function test_inactive_domain_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Inactive Tester',
            'university_id' => 'WU/112233/14',
            'email' => 'tester@inactive.edu.et',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[DataProvider('publicEmailProvider')]
    public function test_public_email_providers_are_rejected(string $email): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Public Provider Test',
            'university_id' => 'WU/' . rand(10000, 99999) . '/14',
            'email' => $email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public static function publicEmailProvider(): array
    {
        return [
            'gmail'      => ['student@gmail.com'],
            'yahoo'      => ['student@yahoo.com'],
            'outlook'    => ['student@outlook.com'],
            'hotmail'    => ['student@hotmail.com'],
            'protonmail' => ['student@protonmail.com'],
            'icloud'     => ['student@icloud.com'],
        ];
    }

    public function test_registration_rate_limiting_enforced(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/register', [
                'full_name' => 'Rate Limit Test ' . $i,
                'university_id' => 'WU/' . (10000 + $i) . '/14',
                'email' => "user{$i}@wu.edu.et",
            ]);
        }

        // 6th request from same IP must hit 429
        $sixth = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Rate Limit Sixth',
            'university_id' => 'WU/99999/14',
            'email' => 'user6@wu.edu.et',
        ]);

        $sixth->assertStatus(429);
    }

    #[DataProvider('lookalikeDomainProvider')]
    public function test_malicious_subdomain_and_lookalikes_are_rejected(string $email): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Attacker',
            'university_id' => 'WU/' . rand(10000, 99999) . '/14',
            'email' => $email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public static function lookalikeDomainProvider(): array
    {
        return [
            'evil prefix'      => ['student@evil-wu.edu.et'],
            'evil tld suffix'  => ['student@wu.edu.et.evil.com'],
            'double at'        => ['student@wu.edu.et@attacker.com'],
            'fake prefix'      => ['student@fake-wu.edu.et'],
            'org suffix'       => ['student@wu.edu.et.attacker.org'],
        ];
    }

    public function test_uppercase_and_whitespace_in_email_are_normalized(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Normalized Student',
            'university_id' => 'WU/887766/14',
            'email' => '  STUDENT@WU.EDU.ET  ',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'student@wu.edu.et');

        $this->assertDatabaseHas('users', [
            'email' => 'student@wu.edu.et',
            'university_id' => 'WU/887766/14',
        ]);
    }

    public function test_otp_verification_flow_generates_server_side_credentials_and_activates_user(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'student.verify@wu.edu.et',
            'password' => null,
            'is_active' => false,
            'email_verified_at' => null,
            'must_change_password' => true,
        ]);

        $otp = '654321';
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

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissing(['password', 'otp', 'token']);

        $refreshed = $user->fresh();
        $this->assertTrue($refreshed->is_active);
        $this->assertTrue($refreshed->must_change_password);
        $this->assertNotNull($refreshed->email_verified_at);
        $this->assertNotNull($refreshed->password);
        $this->assertTrue(str_starts_with($refreshed->password, '$2y$'));

        Queue::assertPushed(SendTemporaryCredential::class);
    }

    public function test_otp_attempt_limits_and_exhaustion(): void
    {
        $user = User::factory()->create([
            'email' => 'student.attempts@wu.edu.et',
            'password' => null,
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        $otp = '888999';
        $verification = AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
            'code' => $otp,
            'token' => Hash::make($otp),
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // 4 failed attempts
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->postJson('/api/v1/auth/verify-email', [
                'email' => $user->email,
                'code' => '000000',
            ]);
            $res->assertStatus(400);
        }

        // 5th failed attempt should lock / exceed
        $res5 = $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '000000',
        ]);
        $res5->assertStatus(429);

        // 6th attempt with correct OTP is now blocked
        $res6 = $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => $otp,
        ]);
        $res6->assertStatus(429);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'student.expired@wu.edu.et',
            'password' => null,
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'email_verification',
            'code' => '112233',
            'token' => Hash::make('112233'),
            'attempts' => 0,
            'last_sent_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-email', [
            'email' => $user->email,
            'code' => '112233',
        ]);

        $response->assertStatus(400);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_first_login_returns_must_change_password_and_clears_after_change(): void
    {
        $rawTempPassword = 'WolloTemp#2026!Sec';
        $user = User::factory()->create([
            'email' => 'firstlogin@wu.edu.et',
            'password' => Hash::make($rawTempPassword),
            'is_active' => true,
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        // Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'firstlogin@wu.edu.et',
            'password' => $rawTempPassword,
        ]);

        $loginRes->assertOk()
            ->assertJsonPath('user.must_change_password', true);

        // Password change
        $newPermanentPassword = 'PermanentSecurePassword2026!#';
        $changeRes = $this->actingAs($user)
            ->putJson('/api/v1/auth/password', [
                'current_password' => $rawTempPassword,
                'new_password' => $newPermanentPassword,
                'new_password_confirmation' => $newPermanentPassword,
            ]);

        $changeRes->assertOk();

        $refreshed = $user->fresh();
        $this->assertFalse($refreshed->must_change_password);
        $this->assertTrue(Hash::check($newPermanentPassword, $refreshed->password));

        // Password history check: cannot reuse previous password
        $reuseRes = $this->actingAs($refreshed)
            ->putJson('/api/v1/auth/password', [
                'current_password' => $newPermanentPassword,
                'new_password' => $rawTempPassword,
                'new_password_confirmation' => $rawTempPassword,
            ]);

        $reuseRes->assertStatus(422)
            ->assertJsonPath('message', 'You cannot reuse any of your previous 5 passwords.');
    }
}
