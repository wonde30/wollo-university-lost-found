<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $categories = Category::where('is_active', true)
                ->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%");
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'data' => CategoryResource::collection($categories),
            ]);
        }

        $data = Cache::remember('categories.all', 3600, function () {
            $categories = Category::where('is_active', true)
                ->withCount(['items' => function ($query) {
                    $query->where('is_deleted', false)
                          ->where('status', '!=', 'withdrawn');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return CategoryResource::collection($categories)->resolve();
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}


