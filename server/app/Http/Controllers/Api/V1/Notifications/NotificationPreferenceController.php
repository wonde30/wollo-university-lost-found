<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notifications\UpdateNotificationPreferencesRequest;
use App\Http\Resources\Api\V1\NotificationPreferenceResource;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prefs = NotificationPreference::where('user_id', $request->user()->id)->get();

        return response()->json([
            'data' => NotificationPreferenceResource::collection($prefs),
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        foreach ($request->validated('preferences') as $pref) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'channel' => $pref['channel'],
                    'notification_type' => $pref['notification_type'],
                ],
                [
                    'is_enabled' => $pref['is_enabled'],
                ]
            );
        }

        $all = NotificationPreference::where('user_id', $userId)->get();

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'data' => NotificationPreferenceResource::collection($all),
        ]);
    }
}
