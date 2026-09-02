<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Models\AuthVerification;
use App\Models\PasswordHistory;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $code = (string) rand(100000, 999999);
        $token = Str::random(60);
        $otpMinutes = (int) SystemSetting::get('otp_expiry_minutes', 10);

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
            'code' => $code,
            'token' => $token,
            'attempts' => 1,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes($otpMinutes), // Configurable via SystemSetting
            'ip_address' => $request->ip(),
        ]);

        \App\Jobs\SendPasswordResetOtp::dispatch($user, $code);

        return response()->json([
            'message' => 'Password reset OTP has been sent to your email.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $verification = AuthVerification::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (! $verification) {
            return response()->json([
                'message' => __('auth.otp_invalid'),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $verification->token,
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $verification = AuthVerification::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (! $verification) {
            return response()->json([
                'message' => __('auth.otp_invalid'),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // FR-05: Previous 5 hashes checked — cannot reuse
        $previousHashes = PasswordHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->pluck('password_hash');

        foreach ($previousHashes as $oldHash) {
            if (Hash::check($request->password, $oldHash)) {
                return response()->json([
                    'message' => 'You cannot reuse any of your previous 5 passwords.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Also check current password
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'You cannot reuse your current password.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($user, $verification, $request) {
            // Store the current hash in history before changing
            PasswordHistory::create([
                'user_id' => $user->id,
                'password_hash' => $user->password,
                'created_at' => now(),
            ]);

            $verification->update(['verified_at' => now()]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        });

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}

