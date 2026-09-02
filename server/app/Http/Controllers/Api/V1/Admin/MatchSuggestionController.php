<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MatchSuggestionResource;
use App\Models\MatchSuggestion;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * FR-51: Staff can review match_suggestions and mark as confirmed or dismissed.
 */
class MatchSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $suggestions = MatchSuggestion::with(['foundItem', 'lostItem'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->has('min_score'), fn ($q) => $q->where('score', '>=', $request->float('min_score')))
            ->orderByDesc('score')
            ->paginate($perPage);

        return response()->json([
            'data' => MatchSuggestionResource::collection($suggestions),
            'meta' => [
                'current_page' => $suggestions->currentPage(),
                'last_page'    => $suggestions->lastPage(),
                'per_page'     => $suggestions->perPage(),
                'total'        => $suggestions->total(),
                'from'         => $suggestions->firstItem(),
                'to'           => $suggestions->lastItem(),
            ],
        ]);
    }

    /**
     * FR-51: Confirm or dismiss a match suggestion.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'dismissed', 'accepted', 'rejected'])],
        ]);

        $suggestion = MatchSuggestion::findOrFail($id);
        $user = $request->user();

        $oldStatus = $suggestion->status;

        $suggestion->update([
            'status'      => $request->status,
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        AuditLogger::log(
            'match_suggestion.' . $request->status,
            $suggestion,
            ['status' => $oldStatus],
            ['status' => $request->status],
            $user
        );

        return response()->json([
            'message' => "Match suggestion {$request->status} successfully.",
            'data'    => new MatchSuggestionResource($suggestion->load(['foundItem', 'lostItem'])),
        ]);
    }
}
