<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Helper for central controllers that act on the user's "current" organization.
 * Central routes are not tenant-prefixed, so the active org is read from the
 * authenticated user's current_tenant_id rather than from the URL.
 */
trait ResolvesCurrentTenant
{
    protected function currentTenant(Request $request): Tenant
    {
        $user = $request->user();
        $tenant = $user->currentTenant;

        abort_unless($tenant && $user->belongsToTenant($tenant), 403,
            'You do not have an active organization.');

        return $tenant;
    }
}
