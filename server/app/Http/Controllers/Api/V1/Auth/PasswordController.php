<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Models\PasswordHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Change authenticated user's password.
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // FR-05: Previous 5 hashes checked — cannot reuse
        $previousHashes = PasswordHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->pluck('password_hash');

        foreach ($previousHashes as $oldHash) {
            if (Hash::check($request->new_password, $oldHash)) {
                return response()->json([
                    'message' => 'You cannot reuse any of your previous 5 passwords.',
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        DB::transaction(function () use ($user, $request) {
            // Store the current hash in history before changing
            PasswordHistory::create([
                'user_id' => $user->id,
                'password_hash' => $user->password,
                'created_at' => now(),
            ]);

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
        });

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}

