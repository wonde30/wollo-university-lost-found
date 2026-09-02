<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set app locale based on Accept-Language header or user setting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');
        if ($locale) {
            $lang = strtolower(substr($locale, 0, 2));
            if (in_array($lang, ['en', 'am'], true)) {
                app()->setLocale($lang);
            }
        } elseif ($user = $request->user()) {
            if ($user->language && in_array($user->language, ['en', 'am'], true)) {
                app()->setLocale($user->language);
            }
        }

        return $next($request);
    }
}
