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

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator.',
            ], 403);
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
        if (in_array('admin', $roles, true) && $user->hasAnyPermission([
            'ACCESS_ADMIN_DASHBOARD',
            'MANAGE_USERS',
            'MANAGE_PERMISSIONS',
            'MANAGE_CAMPUSES',
            'MANAGE_CATEGORIES',
            'MANAGE_LOCATIONS',
            'MANAGE_SETTINGS',
            'VIEW_AUDIT_LOGS',
            'GENERATE_REPORTS',
        ])) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden. You do not have the required role privileges.',
        ], 403);
    }
}
