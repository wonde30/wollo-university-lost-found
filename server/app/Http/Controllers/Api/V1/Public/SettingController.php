<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Return all public settings (is_public = true) for frontend bootstrapping.
     * This endpoint is unauthenticated so the frontend can load branding before login.
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember('settings.public', 3600, function () {
            return SystemSetting::where('is_public', true)
                ->orderBy('key')
                ->get()
                ->mapWithKeys(fn (SystemSetting $s) => [
                    $s->key => $this->castValue($s),
                ])
                ->all();
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Upload institution logo. Admin-only (guarded by route middleware).
     * Stores in public/images/ and updates the logo_url setting.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $file = $request->file('logo');
        $filename = 'institution-logo.' . $file->getClientOriginalExtension();

        // Store in public disk under images/
        $path = $file->storeAs('images', $filename, 'public');

        $logoUrl = '/storage/' . $path;

        // Update system setting
        SystemSetting::updateOrCreate(
            ['key' => 'logo_url'],
            ['value' => $logoUrl]
        );

        Cache::forget('settings.all');
        Cache::forget('settings.public');
        SystemSetting::flushCache();

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'data' => ['logo_url' => $logoUrl],
        ]);
    }

    private function castValue(SystemSetting $setting): mixed
    {
        return match ($setting->type) {
            'integer', 'int'  => (int) $setting->value,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array'   => json_decode($setting->value, true),
            'float', 'double' => (float) $setting->value,
            default           => $setting->value,
        };
    }
}
