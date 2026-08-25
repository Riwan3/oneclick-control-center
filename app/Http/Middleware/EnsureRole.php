<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend authorization aligned with the Control Center role keys
 * (super_admin / management / view — see resources/js/config/roles.js).
 * This checks the role directly rather than mirroring the full frontend
 * capability matrix in PHP; today only super_admin-gated actions exist
 * on the backend, so a role check is sufficient. If more backend
 * actions need finer-grained capability checks later, promote this to
 * a shared capability matrix instead of duplicating role lists further.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
