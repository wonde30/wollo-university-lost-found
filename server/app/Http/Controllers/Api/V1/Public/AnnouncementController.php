<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AnnouncementResource;
use App\Models\SystemAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $now = now();
        $user = $request->user('sanctum') ?? $request->user();

        // Determine accessible audience types based on authenticated user role
        $allowedAudiences = ['all'];
        if ($user) {
            if ($user->isAdmin()) {
                $allowedAudiences = ['all', 'students', 'staff', 'admin'];
            } elseif ($user->isStaff() || $user->isOfficer()) {
                $allowedAudiences = ['all', 'staff'];
            } elseif ($user->isStudent()) {
                $allowedAudiences = ['all', 'students'];
            }
        }

        $announcements = SystemAnnouncement::with('createdByUser')
            ->where('is_active', true)
            ->whereIn('audience', $allowedAudiences)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => AnnouncementResource::collection($announcements),
        ]);
    }
}
