<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Handles credential-based authentication.
 *
 * Security measures implemented (FR-03):
 *  - Failed-attempt counter incremented on every bad password.
 *  - Account locked for 30 minutes after 5 consecutive failures.
 *  - Counter reset to 0 on successful login.
 *  - Inactive accounts rejected with 403.
 */
class LoginController extends Controller
{
    /** Maximum consecutive failures before lockout (FR-03). */
    private const MAX_ATTEMPTS = 5;

    /** Lockout duration in minutes (FR-03). */
    private const LOCKOUT_MINUTES = 30;

    public function login(LoginRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = User::with(['profile', 'departments'])
            ->where('email', $request->email)
            ->first();

        // --- Lockout gate (check before password to avoid timing-based enumeration) ---
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $minutesLeft = (int) now()->diffInMinutes($user->locked_until, false);

            return response()->json([
                'message' => "Account locked due to too many failed attempts. Try again in {$minutesLeft} minute(s).",
            ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
        }

        // --- Credential verification ---
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Increment failure counter (only if user exists)
            if ($user) {
                $attempts = $user->failed_login_attempts + 1;

                $updatePayload = ['failed_login_attempts' => $attempts];

                if ($attempts >= self::MAX_ATTEMPTS) {
                    $updatePayload['locked_until'] = now()->addMinutes(self::LOCKOUT_MINUTES);
                }

                $user->update($updatePayload);
            }

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // --- Account active check ---
        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact the ICT office.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        // --- Successful login: reset failure counter ---
        if ($user->failed_login_attempts > 0 || $user->locked_until !== null) {
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        }

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Login successful.',
            'user'    => new AuthUserResource($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'departments']);

        return response()->json([
            'user' => new AuthUserResource($user),
        ]);
    }
}
