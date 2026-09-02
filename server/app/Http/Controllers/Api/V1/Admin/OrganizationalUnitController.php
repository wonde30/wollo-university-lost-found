<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrganizationalUnitResource;
use App\Models\OrganizationalUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationalUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganizationalUnit::class);

        $query = OrganizationalUnit::with(['campus', 'type', 'parent'])
            ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
            ->when($request->filled('type_id'), fn ($q) => $q->where('type_id', $request->integer('type_id')))
            ->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', $request->integer('parent_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%")
                        ->orWhere('short_code', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name');

        if ($request->boolean('all') && ! $request->filled('search') && ! $request->filled('campus_id') && ! $request->filled('type_id') && ! $request->filled('parent_id')) {
            $data = \Illuminate\Support\Facades\Cache::remember('org_units.all', 3600, function () {
                $units = OrganizationalUnit::with(['campus', 'type', 'parent'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
                return OrganizationalUnitResource::collection($units)->resolve();
            });

            return response()->json([
                'data' => $data,
            ]);
        }

        if ($request->boolean('all')) {
            return response()->json([
                'data' => OrganizationalUnitResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $units = $query->paginate($perPage);

        return response()->json([
            'data' => OrganizationalUnitResource::collection($units),
            'meta' => [
                'current_page' => $units->currentPage(),
                'last_page'    => $units->lastPage(),
                'per_page'     => $units->perPage(),
                'total'        => $units->total(),
                'from'         => $units->firstItem(),
                'to'           => $units->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganizationalUnit::class);

        $validated = $request->validate([
            'campus_id'   => ['required', 'integer', 'exists:campuses,id'],
            'parent_id'   => ['nullable', 'integer', 'exists:organizational_units,id'],
            'type_id'     => ['required', 'integer', 'exists:organizational_unit_types,id'],
            'name'        => ['required', 'string', 'max:255'],
            'name_am'     => ['nullable', 'string', 'max:255'],
            'short_code'  => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $unit = DB::transaction(function () use ($validated) {
            return OrganizationalUnit::create($validated);
        });

        \Illuminate\Support\Facades\Cache::forget('org_units.all');

        return response()->json([
            'message' => 'Organizational unit created successfully',
            'data'    => new OrganizationalUnitResource($unit->load(['campus', 'type'])),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $unit = OrganizationalUnit::with(['campus', 'type', 'parent', 'children'])->findOrFail($id);
        $this->authorize('view', $unit);

        return response()->json([
            'data' => new OrganizationalUnitResource($unit),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $unit = OrganizationalUnit::findOrFail($id);
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'campus_id'   => ['sometimes', 'integer', 'exists:campuses,id'],
            'parent_id'   => ['nullable', 'integer', 'exists:organizational_units,id'],
            'type_id'     => ['sometimes', 'integer', 'exists:organizational_unit_types,id'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'name_am'     => ['nullable', 'string', 'max:255'],
            'short_code'  => ['sometimes', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($unit, $validated) {
            $unit->update($validated);
        });

        \Illuminate\Support\Facades\Cache::forget('org_units.all');

        return response()->json([
            'message' => 'Organizational unit updated successfully',
            'data'    => new OrganizationalUnitResource($unit->load(['campus', 'type'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $unit = OrganizationalUnit::findOrFail($id);
        $this->authorize('delete', $unit);

        DB::transaction(function () use ($unit) {
            $unit->delete();
        });

        \Illuminate\Support\Facades\Cache::forget('org_units.all');

        return response()->json([
            'message' => 'Organizational unit deleted successfully',
        ]);
    }
}

