<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Jobs\SendPasswordResetOtp;
use App\Jobs\SendRegistrationOtp;
use App\Jobs\SendTemporaryCredential;
use App\Models\AuthVerification;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->input('email')));
        $code = trim($request->input('code'));

        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'message' => __('auth.otp_invalid'),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var AuthVerification|null $verification */
        $verification = AuthVerification::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->whereNull('verified_at')
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (! $verification) {
            return response()->json([
                'message' => __('auth.otp_invalid'),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check expiration
        if ($verification->expires_at && $verification->expires_at->isPast()) {
            return response()->json([
                'message' => 'The verification code has expired. Please request a new one.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check attempt threshold (Max 5 attempts)
        if ($verification->attempts >= 5) {
            return response()->json([
                'message' => 'Maximum verification attempts exceeded. Please request a new verification code.',
            ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
        }

        // Validate code (support direct code match or hashed token)
        $matches = ($verification->code === $code) || ($verification->token && Hash::check($code, $verification->token));

        if (! $matches) {
            $verification->increment('attempts');
            $remaining = 5 - $verification->attempts;

            if ($remaining <= 0) {
                return response()->json([
                    'message' => 'Maximum verification attempts exceeded. Please request a new verification code.',
                ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
            }

            return response()->json([
                'message' => "Invalid verification code. {$remaining} attempt(s) remaining.",
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // OTP verified successfully: generate secure server-side temporary password
        $temporaryPassword = $this->generateSecureTemporaryPassword(16);

        DB::transaction(function () use ($verification, $user, $temporaryPassword) {
            $verification->update([
                'verified_at' => now(),
                'used_at'     => now(),
            ]);

            $user->update([
                'email_verified_at'    => now(),
                'password'             => Hash::make($temporaryPassword),
                'is_active'            => true,
                'must_change_password' => true,
            ]);

            SendTemporaryCredential::dispatch($user, $temporaryPassword);
        });

        return response()->json([
            'message' => 'Your email has been verified. Your account credentials have been sent to your university email.',
            'data'    => [
                'email' => $user->email,
            ],
        ], JsonResponse::HTTP_OK);
    }

    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->firstOrFail();
        $type = $request->input('type', 'email_verification');

        // Check 60-second cooldown since last OTP send
        $lastVerification = AuthVerification::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->first();

        if ($lastVerification && $lastVerification->last_sent_at) {
            $cooldownExpiresAt = $lastVerification->last_sent_at->copy()->addSeconds(60);
            if (now()->lt($cooldownExpiresAt)) {
                $remaining = max(1, (int) ceil(now()->diffInSeconds($cooldownExpiresAt)));
                return response()->json([
                    'message' => "Please wait {$remaining} second(s) before requesting another OTP.",
                ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
            }
        }

        // Maximum 3 resend attempts per session
        $resendCount = AuthVerification::where('user_id', $user->id)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->count();

        if ($resendCount >= 3) {
            return response()->json([
                'message' => 'Maximum OTP resend attempts reached. Please wait or contact support.',
            ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
        }

        // Invalidate previous pending OTPs
        AuthVerification::where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        $code = (string) random_int(100000, 999999);
        $otpMinutes = (int) SystemSetting::get('otp_expiry_minutes', 10);

        AuthVerification::create([
            'user_id'      => $user->id,
            'email'        => $user->email,
            'type'         => $type,
            'code'         => $code,
            'token'        => Hash::make($code),
            'attempts'     => 0,
            'last_sent_at' => now(),
            'expires_at'   => now()->addMinutes($otpMinutes),
            'ip_address'   => $request->ip(),
        ]);

        if ($type === 'password_reset') {
            SendPasswordResetOtp::dispatch($user, $code);
        } else {
            SendRegistrationOtp::dispatch($user, $code);
        }

        return response()->json([
            'message' => __('auth.otp_sent'),
        ]);
    }

    /**
     * Generate a cryptographically secure, high-entropy temporary password.
     */
    protected function generateSecureTemporaryPassword(int $length = 16): string
    {
        $uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowers = 'abcdefghijkmnopqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%^&*()-_+=~';

        // Ensure representation from all 4 character sets
        $chars = [
            $uppers[random_int(0, strlen($uppers) - 1)],
            $uppers[random_int(0, strlen($uppers) - 1)],
            $lowers[random_int(0, strlen($lowers) - 1)],
            $lowers[random_int(0, strlen($lowers) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        $all = $uppers . $lowers . $numbers . $symbols;
        $remaining = max(0, $length - count($chars));
        for ($i = 0; $i < $remaining; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Fisher-Yates shuffle
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $temp = $chars[$i];
            $chars[$i] = $chars[$j];
            $chars[$j] = $temp;
        }

        return implode('', $chars);
    }
}

