<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden.'], 403)
                : abort(403, 'Unauthorized action. You do not have the required role.');
        }

        return $next($request);
    }
}
