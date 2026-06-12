<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The post-login landing route. It owns no view of its own — it simply routes the
 * user into their current organization, or to the create-organization screen if
 * they don't belong to one yet.
 */
class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = $user->currentTenant;

        // Fall back to any organization the user belongs to.
        if (! $tenant || ! $user->belongsToTenant($tenant)) {
            $tenant = $user->tenants()->first();

            if (! $tenant) {
                return redirect()->route('organizations.create');
            }

            $user->forceFill(['current_tenant_id' => $tenant->id])->save();
        }

        return redirect()->route('tenant.dashboard', ['tenant' => $tenant->id]);
    }
}
