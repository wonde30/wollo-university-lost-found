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

        // Consolidated item counts (1 query instead of 3)
        $itemCounts = \App\Models\Item::where('reporter_id', $user->id)
            ->where('is_deleted', false)
            ->selectRaw("
                SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_count,
                SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_count,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_count
            ")
            ->first();

        // Consolidated claim counts (1 query instead of 3)
        $claimCounts = \App\Models\Claim::where('claimant_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('pending', 'under_review') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
            ")
            ->first();

        // Resolved = approved claims with confirmed return (still needs subquery)
        $resolvedClaimsCount = \App\Models\Claim::where('claimant_id', $user->id)
            ->where('status', 'approved')
            ->whereHas('returnRecord', fn ($rq) => $rq->where('recipient_confirmed', true)->orWhereNotNull('confirmed_at'))
            ->count();

        return response()->json([
            'data' => [
                'my_lost_count'         => (int) ($itemCounts->lost_count ?? 0),
                'my_found_count'        => (int) ($itemCounts->found_count ?? 0),
                'my_claims_count'       => (int) ($claimCounts->total ?? 0),
                'active_claims_count'   => (int) ($claimCounts->active ?? 0),
                'resolved_claims_count' => $resolvedClaimsCount,
                'returned_items_count'  => (int) ($itemCounts->returned_count ?? 0),
            ],
        ]);
    }
}

