<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateSystemSettingRequest;
use App\Http\Resources\Api\V1\SystemSettingResource;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $query = SystemSetting::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('key', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_public'), fn ($q) => $q->where('is_public', $request->boolean('is_public')))
            ->orderBy('key');

        if ($request->boolean('all') && ! $request->filled('search')) {
            $data = \Illuminate\Support\Facades\Cache::remember('settings.all', 3600, function () {
                $settings = SystemSetting::orderBy('key')->get();
                return SystemSettingResource::collection($settings)->resolve();
            });

            return response()->json([
                'data' => $data,
            ]);
        }

        if ($request->boolean('all')) {
            return response()->json([
                'data' => SystemSettingResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $settings = $query->paginate($perPage);

        return response()->json([
            'data' => SystemSettingResource::collection($settings),
            'meta' => [
                'current_page' => $settings->currentPage(),
                'last_page'    => $settings->lastPage(),
                'per_page'     => $settings->perPage(),
                'total'        => $settings->total(),
                'from'         => $settings->firstItem(),
                'to'           => $settings->lastItem(),
            ],
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, string $key): JsonResponse
    {
        $this->authorize('update', SystemSetting::class);

        $setting = SystemSetting::where('key', $key)->firstOrFail();
        $setting->update($request->validated());

        \Illuminate\Support\Facades\Cache::forget('settings.all');
        \Illuminate\Support\Facades\Cache::forget('settings.public');
        SystemSetting::flushCache();

        return response()->json([
            'message' => 'System setting updated successfully',
            'data' => new SystemSettingResource($setting),
        ]);
    }
}

