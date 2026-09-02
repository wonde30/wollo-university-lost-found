<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationalUnitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationalUnitTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganizationalUnitType::class);

        $query = OrganizationalUnitType::with(['childTypeRelations.childType', 'parentTypeRelations.parentType'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_root'), fn ($q) => $q->where('is_root', $request->boolean('is_root')))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('is_root', 'desc')
            ->orderBy('name');

        if ($request->boolean('all') && ! $request->filled('search')) {
            $data = \Illuminate\Support\Facades\Cache::remember('org_unit_types.all', 3600, function () {
                return OrganizationalUnitType::with(['childTypeRelations.childType', 'parentTypeRelations.parentType'])
                    ->where('is_active', true)
                    ->orderBy('is_root', 'desc')
                    ->orderBy('name')
                    ->get();
            });

            return response()->json([
                'data' => $data,
            ]);
        }

        if ($request->boolean('all')) {
            return response()->json([
                'data' => $query->get(),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $types = $query->paginate($perPage);

        return response()->json([
            'data' => $types->items(),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page'    => $types->lastPage(),
                'per_page'     => $types->perPage(),
                'total'        => $types->total(),
                'from'         => $types->firstItem(),
                'to'           => $types->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganizationalUnitType::class);

        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:50', 'unique:organizational_unit_types,code'],
            'name'        => ['required', 'string', 'max:150', 'unique:organizational_unit_types,name'],
            'name_am'     => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_root'     => ['nullable', 'boolean'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $type = DB::transaction(function () use ($validated) {
            return OrganizationalUnitType::create($validated);
        });

        \Illuminate\Support\Facades\Cache::forget('org_unit_types.all');

        return response()->json([
            'message' => 'Organizational unit type created successfully.',
            'data'    => $type,
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $type = OrganizationalUnitType::with(['childTypeRelations.childType', 'parentTypeRelations.parentType', 'organizationalUnits'])
            ->findOrFail($id);
        $this->authorize('view', $type);

        return response()->json([
            'data' => $type,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $type = OrganizationalUnitType::findOrFail($id);
        $this->authorize('update', $type);

        $validated = $request->validate([
            'code'        => ['sometimes', 'string', 'max:50', "unique:organizational_unit_types,code,{$id}"],
            'name'        => ['sometimes', 'string', 'max:150', "unique:organizational_unit_types,name,{$id}"],
            'name_am'     => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_root'     => ['nullable', 'boolean'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($type, $validated) {
            $type->update($validated);
        });

        \Illuminate\Support\Facades\Cache::forget('org_unit_types.all');

        return response()->json([
            'message' => 'Organizational unit type updated successfully.',
            'data'    => $type,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $type = OrganizationalUnitType::findOrFail($id);
        $this->authorize('delete', $type);

        DB::transaction(function () use ($type) {
            $type->delete();
        });

        \Illuminate\Support\Facades\Cache::forget('org_unit_types.all');

        return response()->json([
            'message' => 'Organizational unit type deleted successfully.',
        ]);
    }
}

