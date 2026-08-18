<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateSystemSettingRequest;
use App\Http\Resources\Api\V1\SystemSettingResource;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SystemSetting::all();

        return response()->json([
            'data' => SystemSettingResource::collection($settings),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, string $key): JsonResponse
    {
        $this->authorize('update', SystemSetting::class);

        $setting = SystemSetting::where('key', $key)->firstOrFail();
        $setting->update($request->validated());

        return response()->json([
            'message' => 'System setting updated successfully',
            'data' => new SystemSettingResource($setting),
        ]);
    }
}
