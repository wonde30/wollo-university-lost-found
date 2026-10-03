<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\UniversityDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UniversityDomainController extends Controller
{
    /**
     * Get list of active university domains for frontend registration discovery/UX.
     */
    public function index(): JsonResponse
    {
        $domains = Cache::remember('public.university_domains', 3600, function () {
            return UniversityDomain::query()
                ->active()
                ->select(['domain', 'institution_name'])
                ->orderBy('domain')
                ->get()
                ->map(fn ($d) => [
                    'domain'           => $d->domain,
                    'institution_name' => $d->institution_name,
                ])
                ->values()
                ->all();
        });

        return response()->json([
            'data' => $domains,
        ]);
    }
}
