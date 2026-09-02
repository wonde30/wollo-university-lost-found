<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Models\AuthVerification;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $verification = AuthVerification::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (! $verification) {
            return response()->json([
                'message' => __('auth.otp_invalid'),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        DB::transaction(function () use ($verification, $user) {
            $verification->update(['verified_at' => now()]);
            $user->update(['email_verified_at' => now()]);
        });

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $type = $request->input('type', 'email_verification');

        // FR-02: Check 60-second cooldown since last OTP send
        $lastVerification = AuthVerification::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->first();

        if ($lastVerification && $lastVerification->last_sent_at) {
            $secondsSinceLastSend = now()->diffInSeconds($lastVerification->last_sent_at);
            if ($secondsSinceLastSend < 60) {
                $remaining = 60 - $secondsSinceLastSend;
                return response()->json([
                    'message' => "Please wait {$remaining} second(s) before requesting another OTP.",
                ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
            }
        }

        // FR-02: Maximum 3 resend attempts per registration session
        $resendCount = AuthVerification::where('user_id', $user->id)
            ->where('type', $type)
            ->count();

        if ($resendCount >= 3) {
            return response()->json([
                'message' => 'Maximum OTP resend attempts reached. Please contact support.',
            ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
        }

        $code = (string) rand(100000, 999999);
        $otpMinutes = (int) SystemSetting::get('otp_expiry_minutes', 10);

        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $type,
            'code' => $code,
            'token' => \Illuminate\Support\Facades\Hash::make($code),
            'attempts' => $resendCount + 1,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes($otpMinutes), // Configurable via SystemSetting
            'ip_address' => $request->ip(),
        ]);

        if ($type === 'password_reset') {
            \App\Jobs\SendPasswordResetOtp::dispatch($user, $code);
        } else {
            \App\Jobs\SendRegistrationOtp::dispatch($user, $code);
        }

        return response()->json([
            'message' => __('auth.otp_sent'),
        ]);
    }
}

