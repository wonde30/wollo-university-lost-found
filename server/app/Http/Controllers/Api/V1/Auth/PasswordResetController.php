<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Models\AuthVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $code = (string) rand(100000, 999999);
        $token = Str::random(60);

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => 'password_reset',
            'code' => $code,
            'token' => $token,
            'attempts' => 1,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(15),
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

        $verification->update(['verified_at' => now()]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
