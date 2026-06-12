<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on tenant routes AFTER tenancy has been initialized. Confirms the
 * authenticated user is actually a member of the resolved tenant — without this,
 * any logged-in user could load /someone-elses-org/dashboard. Also keeps the
 * user's current_tenant_id in sync so the central dashboard reflects reality.
 */
class EnsureMemberOfTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /** @var Tenant|null $tenant */
        $tenant = tenant();

        abort_unless($user && $tenant && $user->belongsToTenant($tenant), 403,
            'You are not a member of this organization.');

        if ($user->current_tenant_id !== $tenant->id) {
            $user->forceFill(['current_tenant_id' => $tenant->id])->save();
        }

        // Let route('tenant.*') links omit the {tenant} parameter within context.
        URL::defaults(['tenant' => $tenant->id]);

        return $next($request);
    }
}
