<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userRole = $user->getRoleName();

        // Admin has super-user access across staff endpoints as per FR-12
        if ($userRole === 'admin') {
            return $next($request);
        }

        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        // Allow custom roles that hold staff-level operational capabilities
        if (in_array('staff', $roles, true) && $user->isOfficer()) {
            return $next($request);
        }

        // Allow custom roles that hold admin dashboard or management capabilities
        if (in_array('admin', $roles, true) && (
            $user->hasPermission('ACCESS_ADMIN_DASHBOARD')
            || $user->hasPermission('MANAGE_USERS')
            || $user->hasPermission('MANAGE_PERMISSIONS')
            || $user->hasPermission('MANAGE_CAMPUSES')
            || $user->hasPermission('MANAGE_CATEGORIES')
            || $user->hasPermission('MANAGE_LOCATIONS')
            || $user->hasPermission('MANAGE_SETTINGS')
            || $user->hasPermission('VIEW_AUDIT_LOGS')
            || $user->hasPermission('GENERATE_REPORTS')
        )) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden. You do not have the required role privileges.',
        ], 403);
    }
}
