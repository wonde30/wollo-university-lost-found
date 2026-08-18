<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Models\AuthVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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

        $verification->update(['verified_at' => now()]);
        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $code = (string) rand(100000, 999999);
        AuthVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $request->input('type', 'email_verification'),
            'code' => $code,
            'token' => \Illuminate\Support\Facades\Hash::make($code),
            'attempts' => 1,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => __('auth.otp_sent'),
        ]);
    }
}
