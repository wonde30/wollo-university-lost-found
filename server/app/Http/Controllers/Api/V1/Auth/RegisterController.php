<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Jobs\SendRegistrationOtp;
use App\Models\AuthVerification;
use App\Models\NotificationPreference;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserOrganizationalUnit;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $universityId = trim($validated['university_id']);

        $user = DB::transaction(function () use ($validated, $email, $universityId, $request) {
            $studentRole = Role::where('name', 'student')->first();
            $roleId = $studentRole?->id ?? Role::firstOrCreate(
                ['name' => 'student'],
                ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]
            )->id;

            // Check if there is already an unverified user record with this email or university_id
            $existingUnverifiedUser = User::where(function ($query) use ($email, $universityId) {
                $query->where('email', $email)
                      ->orWhere('university_id', $universityId);
            })->where('is_active', false)
              ->whereNull('email_verified_at')
              ->first();

            if ($existingUnverifiedUser) {
                $user = $existingUnverifiedUser;
                $user->update([
                    'full_name'            => $validated['full_name'],
                    'university_id'        => $universityId,
                    'email'                => $email,
                    'phone'                => $validated['phone'] ?? null,
                    'role_id'              => $roleId,
                    'must_change_password' => true,
                ]);

                UserProfile::firstOrCreate(['user_id' => $user->id]);
                NotificationPreference::firstOrCreate(['user_id' => $user->id]);
            } else {
                $user = User::create([
                    'full_name'            => $validated['full_name'],
                    'university_id'        => $universityId,
                    'email'                => $email,
                    'password'             => null, // Password generated automatically after email OTP verification
                    'phone'                => $validated['phone'] ?? null,
                    'role_id'              => $roleId,
                    'language'             => 'en',
                    'is_active'            => false, // Inactive until email is verified and credentials issued
                    'must_change_password' => true,
                ]);

                UserProfile::create([
                    'user_id' => $user->id,
                ]);

                NotificationPreference::create([
                    'user_id' => $user->id,
                ]);
            }

            if (! empty($validated['organizational_unit_id'])) {
                UserOrganizationalUnit::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'organizational_unit_id' => $validated['organizational_unit_id'],
                        'is_primary'             => true,
                    ]
                );
            }

            // Invalidate any previous pending verifications for this email or user
            AuthVerification::where(function ($q) use ($email, $user) {
                    $q->where('email', $email)->orWhere('user_id', $user->id);
                })
                ->where('type', 'email_verification')
                ->whereNull('verified_at')
                ->update(['verified_at' => now()]);

            $otp = (string) random_int(100000, 999999);
            $otpMinutes = (int) SystemSetting::get('otp_expiry_minutes', 10);

            AuthVerification::create([
                'user_id'      => $user->id,
                'email'        => $user->email,
                'type'         => 'email_verification',
                'code'         => $otp,
                'token'        => Hash::make($otp),
                'attempts'     => 0,
                'last_sent_at' => now(),
                'expires_at'   => now()->addMinutes($otpMinutes),
                'ip_address'   => $request->ip(),
            ]);

            SendRegistrationOtp::dispatch($user, $otp);

            return $user;
        });

        return response()->json([
            'message' => 'Registration initiated. A 6-digit verification code has been sent to your university email.',
            'data'    => [
                'email' => $user->email,
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
