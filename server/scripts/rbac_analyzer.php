<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

$routes = Route::getRoutes();

$report = [];
$unprotected = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    $action = $route->getActionName();
    $middleware = $route->gatherMiddleware();

    // Skip internal/sanctum/storage/up/debug routes
    if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum') || str_starts_with($uri, 'storage') || $uri === 'up' || $uri === '/') {
        continue;
    }

    $hasAuth = false;
    $hasRole = false;
    $hasActive = false;

    foreach ($middleware as $m) {
        if (str_contains($m, 'auth') || str_contains($m, 'Authenticate')) {
            $hasAuth = true;
        }
        if (str_contains($m, 'role') || str_contains($m, 'EnsureUserHasRole')) {
            $hasRole = $m;
        }
        if (str_contains($m, 'active') || str_contains($m, 'EnsureUserIsActive')) {
            $hasActive = true;
        }
    }

    $isPublic = str_starts_with($uri, 'api/v1/public') || str_starts_with($uri, 'api/v1/auth') || str_starts_with($uri, 'api/v1/returns/confirm-token');

    $report[] = [
        'methods' => $methods,
        'uri' => $uri,
        'action' => $action,
        'auth' => $hasAuth,
        'role_middleware' => $hasRole,
        'active' => $hasActive,
        'is_public' => $isPublic,
    ];

    if (!$isPublic && !$hasAuth && !$hasRole) {
        $unprotected[] = "$methods $uri -> $action";
    }
}

file_put_contents(__DIR__ . '/rbac_route_analysis.json', json_encode([
    'total_routes' => count($report),
    'unprotected_count' => count($unprotected),
    'unprotected_routes' => $unprotected,
    'routes' => $report,
], JSON_PRETTY_PRINT));

echo "Analyzed " . count($report) . " routes. Unprotected non-public: " . count($unprotected) . "\n";
