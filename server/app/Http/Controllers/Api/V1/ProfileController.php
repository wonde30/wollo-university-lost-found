<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\UploadAvatarRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $user->load(['profile', 'organizationalUnits']);

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

        $user->load(['profile', 'organizationalUnits']);

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'user' => new AuthUserResource($user),
        ]);
    }

    /**
     * Get personal statistics summary for authenticated user (student/staff).
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $lostCount = \App\Models\Item::where('reporter_id', $user->id)
            ->where('type', 'lost')
            ->where('is_deleted', false)
            ->count();

        $foundCount = \App\Models\Item::where('reporter_id', $user->id)
            ->where('type', 'found')
            ->where('is_deleted', false)
            ->count();

        $claimsCount = \App\Models\Claim::where('claimant_id', $user->id)->count();
        $activeClaimsCount = \App\Models\Claim::where('claimant_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
        $resolvedClaimsCount = \App\Models\Claim::where('claimant_id', $user->id)
            ->where('status', 'approved')
            ->whereHas('returnRecord', fn ($rq) => $rq->where('recipient_confirmed', true)->orWhereNotNull('confirmed_at'))
            ->count();
        $returnedItemsCount = \App\Models\Item::where('reporter_id', $user->id)
            ->where('status', 'returned')
            ->where('is_deleted', false)
            ->count();

        return response()->json([
            'data' => [
                'my_lost_count'         => $lostCount,
                'my_found_count'        => $foundCount,
                'my_claims_count'       => $claimsCount,
                'active_claims_count'   => $activeClaimsCount,
                'resolved_claims_count' => $resolvedClaimsCount,
                'returned_items_count'  => $returnedItemsCount,
            ],
        ]);
    }
}

