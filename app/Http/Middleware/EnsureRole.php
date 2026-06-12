<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Coarse role gate for tenant routes. Usage: ->middleware('tenant.role:admin')
 * grants access to anyone whose role ranks at or above Admin (i.e. Admin or
 * Owner). Fine-grained, per-action checks belong in policies.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $minimum): Response
    {
        $user = $request->user();

        /** @var Tenant|null $tenant */
        $tenant = tenant();

        abort_unless($user && $tenant, 403);

        $required = Role::from($minimum);

        abort_unless($user->hasTenantRole($tenant, $required), 403,
            'You do not have permission to perform this action.');

        return $next($request);
    }
}
