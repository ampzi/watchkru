<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend-enforced role check for admin/moderator routes. Frontend role
 * checks are convenience only — this middleware is the real gate.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی لازم برای این بخش را ندارید.',
            ], 403);
        }

        return $next($request);
    }
}
