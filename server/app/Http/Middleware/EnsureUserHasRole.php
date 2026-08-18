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

        $userRole = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        // Admin has super-user access across staff endpoints as per FR-12
        if ($userRole === 'admin') {
            return $next($request);
        }

        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden. You do not have the required role privileges.',
        ], 403);
    }
}
