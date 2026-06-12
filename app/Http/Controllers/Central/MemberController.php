<?php

namespace App\Http\Controllers\Central;

use App\Enums\Role;
use App\Http\Controllers\Concerns\ResolvesCurrentTenant;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    use ResolvesCurrentTenant;

    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('view', $tenant);

        return view('organization.members', [
            'tenant' => $tenant,
            'members' => $tenant->memberships()->with('user')->get(),
            'invitations' => $tenant->invitations()->whereNull('accepted_at')->get(),
            'canManage' => $request->user()->can('manageMembers', $tenant),
            'assignableRoles' => Role::assignable(),
        ]);
    }

    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageMembers', $tenant);

        abort_unless($membership->tenant_id === $tenant->id, 404);
        abort_if($membership->role === Role::Owner, 403, 'The owner role cannot be changed here.');

        $validated = $request->validate([
            'role' => ['required', Rule::in([Role::Admin->value, Role::Member->value])],
        ]);

        $membership->update(['role' => $validated['role']]);

        return back()->with('status', 'Member role updated.');
    }

    public function destroy(Request $request, Membership $membership): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageMembers', $tenant);

        abort_unless($membership->tenant_id === $tenant->id, 404);
        abort_if($membership->role === Role::Owner, 403, 'The owner cannot be removed.');

        $membership->delete();

        return back()->with('status', 'Member removed.');
    }
}
