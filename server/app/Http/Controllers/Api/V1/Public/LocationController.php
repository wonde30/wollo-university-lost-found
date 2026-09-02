<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->filled('campus_id') || $request->filled('search')) {
            $locations = Location::with('campus')
                ->where('is_active', true)
                ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = trim($request->string('search')->toString());
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('name_am', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('building', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->get();

            return response()->json([
                'data' => LocationResource::collection($locations),
            ]);
        }

        $data = Cache::remember('locations.all', 3600, function () {
            $locations = Location::with('campus')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return LocationResource::collection($locations)->resolve();
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}

