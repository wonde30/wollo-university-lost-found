<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

$routes = Route::getRoutes();

$fullAnalysis = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    $action = $route->getActionName();
    $middleware = $route->gatherMiddleware();

    if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum') || str_starts_with($uri, 'storage') || $uri === 'up' || $uri === '/') {
        continue;
    }

    $controller = null;
    $method = null;
    if (str_contains($action, '@')) {
        [$controller, $method] = explode('@', $action);
    }

    $authMiddleware = in_array('auth:sanctum', $middleware, true) || in_array('Illuminate\Auth\Middleware\Authenticate:sanctum', $middleware, true);
    $activeMiddleware = in_array('active', $middleware, true) || in_array('App\Http\Middleware\EnsureUserIsActive', $middleware, true);
    $roleMiddleware = null;
    foreach ($middleware as $m) {
        if (str_starts_with($m, 'role:') || str_starts_with($m, 'App\Http\Middleware\EnsureUserHasRole:')) {
            $roleMiddleware = str_replace(['role:', 'App\Http\Middleware\EnsureUserHasRole:'], '', $m);
        }
    }

    $hasAuthorizeCall = false;
    $formRequestAuthorize = null;

    if ($controller && class_exists($controller) && method_exists($controller, $method)) {
        $refMethod = new ReflectionMethod($controller, $method);
        $file = $refMethod->getFileName();
        $start = $refMethod->getStartLine();
        $end = $refMethod->getEndLine();
        $lines = array_slice(file($file), $start - 1, $end - $start + 1);
        $body = implode('', $lines);

        if (str_contains($body, '$this->authorize(') || str_contains($body, 'Gate::authorize(')) {
            $hasAuthorizeCall = true;
        }

        foreach ($refMethod->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && class_exists($type->getName())) {
                $cls = $type->getName();
                if (is_subclass_of($cls, Illuminate\Foundation\Http\FormRequest::class)) {
                    $formRequestAuthorize = $cls;
                }
            }
        }
    }

    $fullAnalysis[] = [
        'uri' => $uri,
        'methods' => $methods,
        'controller' => $controller,
        'method' => $method,
        'auth_middleware' => $authMiddleware,
        'active_middleware' => $activeMiddleware,
        'role_middleware' => $roleMiddleware,
        'has_authorize_call' => $hasAuthorizeCall,
        'form_request' => $formRequestAuthorize,
    ];
}

file_put_contents(__DIR__ . '/rbac_detailed_matrix.json', json_encode($fullAnalysis, JSON_PRETTY_PRINT));
echo "Detailed matrix saved for " . count($fullAnalysis) . " routes.\n";
