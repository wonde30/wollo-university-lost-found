<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UploadAvatarRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        $user->load(['profile', 'departments']);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new AuthUserResource($user),
        ]);
    }

    /**
     * Upload profile avatar.
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();

        // Delete old avatar if exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['profile_photo' => $path]);

        $user->load(['profile', 'departments']);

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'user' => new AuthUserResource($user),
        ]);
    }
}
