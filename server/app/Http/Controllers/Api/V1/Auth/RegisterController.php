<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Models\AuthVerification;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserDepartment;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'full_name' => $validated['full_name'],
                'university_id' => $validated['university_id'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'role' => UserRole::STUDENT,
                'language' => 'en',
                'is_active' => true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            if (! empty($validated['department_id'])) {
                UserDepartment::create([
                    'user_id' => $user->id,
                    'department_id' => $validated['department_id'],
                    'is_primary' => true,
                ]);
            }

            $otp = (string) rand(100000, 999999);
            AuthVerification::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => 'email_verification',
                'code' => $otp,
                'token' => Hash::make($otp),
                'attempts' => 0,
                'last_sent_at' => now(),
                'expires_at' => now()->addMinutes(10), // FR-02 (10 minutes)
                'ip_address' => $request->ip(),
            ]);

            return $user;
        });

        $user->load(['profile', 'departments']);
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
