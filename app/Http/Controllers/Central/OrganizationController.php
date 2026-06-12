<?php

namespace App\Http\Controllers\Central;

use App\Actions\ProvisionTenant;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function create(): View
    {
        return view('organizations.create');
    }

    public function store(Request $request, ProvisionTenant $provisioner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant = $provisioner->create($request->user(), $validated['name']);

        return redirect()
            ->route('tenant.dashboard', ['tenant' => $tenant->id])
            ->with('status', 'Organization created.');
    }

    /** Switch the user's active organization. */
    public function switch(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->belongsToTenant($tenant), 403);

        $user->forceFill(['current_tenant_id' => $tenant->id])->save();

        return redirect()->route('tenant.dashboard', ['tenant' => $tenant->id]);
    }

    /** Permanently delete an organization and its database (owner only). */
    public function destroy(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        $user = $request->user();

        // Deleting the tenant fires TenantDeleted -> DeleteDatabase, dropping the
        // tenant database. Memberships/invitations cascade via FK.
        $tenant->delete();

        if ($user->current_tenant_id === $tenant->id) {
            $user->forceFill(['current_tenant_id' => $user->tenants()->value('tenants.id')])->save();
        }

        return redirect()->route('dashboard')->with('status', 'Organization deleted.');
    }
}
