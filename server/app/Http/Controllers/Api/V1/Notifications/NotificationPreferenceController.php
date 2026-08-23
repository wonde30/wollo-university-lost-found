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
        $prefs = NotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        return response()->json([
            'data' => new NotificationPreferenceResource($prefs),
        ]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $prefs = NotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        $prefs->update($request->validated());

        return response()->json([
            'message' => 'Notification preferences updated successfully.',
            'data' => new NotificationPreferenceResource($prefs->fresh()),
        ]);
    }
}
