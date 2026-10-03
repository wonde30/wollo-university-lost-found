<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreUniversityDomainRequest;
use App\Http\Requests\Api\V1\Admin\UpdateUniversityDomainRequest;
use App\Http\Resources\Api\V1\UniversityDomainResource;
use App\Models\UniversityDomain;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UniversityDomainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UniversityDomain::class);

        $query = UniversityDomain::with('campus')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('domain', 'like', "%{$search}%")
                        ->orWhere('institution_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->filled('campus_id'), function ($q) use ($request) {
                $q->where('campus_id', $request->integer('campus_id'));
            })
            ->orderBy('domain');

        if ($request->boolean('all')) {
            return response()->json([
                'data' => UniversityDomainResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 15)));
        $domains = $query->paginate($perPage);

        return response()->json([
            'data' => UniversityDomainResource::collection($domains),
            'meta' => [
                'current_page' => $domains->currentPage(),
                'last_page'    => $domains->lastPage(),
                'per_page'     => $domains->perPage(),
                'total'        => $domains->total(),
                'from'         => $domains->firstItem(),
                'to'           => $domains->lastItem(),
            ],
        ]);
    }

    public function store(StoreUniversityDomainRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $domain = UniversityDomain::create($validated);

        AuditLogger::log('university_domain.created', $domain, null, $domain->toArray(), $request->user());
        UniversityDomain::flushDomainCache();

        return response()->json([
            'message' => 'University domain created successfully.',
            'data'    => new UniversityDomainResource($domain->load('campus')),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int|string $id): JsonResponse
    {
        $domain = UniversityDomain::with('campus')->findOrFail($id);
        $this->authorize('view', $domain);

        return response()->json([
            'data' => new UniversityDomainResource($domain),
        ]);
    }

    public function update(UpdateUniversityDomainRequest $request, int|string $id): JsonResponse
    {
        $domain = UniversityDomain::findOrFail($id);
        $this->authorize('update', $domain);

        $oldData = $domain->toArray();
        $domain->update($request->validated());

        AuditLogger::log('university_domain.updated', $domain, $oldData, $domain->toArray(), $request->user());
        UniversityDomain::flushDomainCache();

        return response()->json([
            'message' => 'University domain updated successfully.',
            'data'    => new UniversityDomainResource($domain->load('campus')),
        ]);
    }

    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $domain = UniversityDomain::findOrFail($id);
        $this->authorize('delete', $domain);

        $oldData = $domain->toArray();
        $domain->delete();

        AuditLogger::log('university_domain.deleted', $domain, $oldData, [], $request->user());
        UniversityDomain::flushDomainCache();

        return response()->json([
            'message' => 'University domain removed successfully.',
        ]);
    }

    public function toggleActive(Request $request, int|string $id): JsonResponse
    {
        $domain = UniversityDomain::findOrFail($id);
        $this->authorize('update', $domain);

        $oldStatus = $domain->is_active;
        $domain->update(['is_active' => ! $oldStatus]);

        $action = $domain->is_active ? 'university_domain.activated' : 'university_domain.deactivated';
        AuditLogger::log($action, $domain, ['is_active' => $oldStatus], ['is_active' => $domain->is_active], $request->user());
        UniversityDomain::flushDomainCache();

        return response()->json([
            'message' => 'University domain status updated successfully.',
            'data'    => new UniversityDomainResource($domain->load('campus')),
        ]);
    }
}
