<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Models\AuthVerification;
use App\Models\NotificationPreference;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserOrganizationalUnit;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated, $request) {
            $studentRole = Role::where('name', 'student')->first();

            $user = User::create([
                'full_name'     => $validated['full_name'],
                'university_id' => $validated['university_id'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'phone'         => $validated['phone'] ?? null,
                'role_id'       => $studentRole?->id ?? Role::firstOrCreate(
                    ['name' => 'student'],
                    ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]
                )->id,
                'language'      => 'en',
                'is_active'     => true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            NotificationPreference::create([
                'user_id' => $user->id,
            ]);

            if (! empty($validated['organizational_unit_id'])) {
                UserOrganizationalUnit::create([
                    'user_id'                  => $user->id,
                    'organizational_unit_id'   => $validated['organizational_unit_id'],
                    'is_primary'               => true,
                ]);
            }

            $otp = (string) rand(100000, 999999);
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

            \App\Jobs\SendRegistrationOtp::dispatch($user, $otp);

            return $user;
        });

        $user->load(['profile', 'organizationalUnits']);
        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'data' => [
                'user' => new AuthUserResource($user),
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
