<?php

namespace App\Http\Controllers\Central;

use App\Actions\AcceptInvitation;
use App\Enums\Role;
use App\Http\Controllers\Concerns\ResolvesCurrentTenant;
use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvitationController extends Controller
{
    use ResolvesCurrentTenant;

    /** Number of days an invitation remains valid. */
    private const EXPIRY_DAYS = 7;

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageMembers', $tenant);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in([Role::Admin->value, Role::Member->value])],
        ]);

        $email = Str::lower($validated['email']);

        // Don't invite someone who is already a member.
        if ($tenant->users()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That person is already a member of this organization.',
            ]);
        }

        $invitation = $tenant->invitations()->updateOrCreate(
            ['email' => $email],
            [
                'role' => $validated['role'],
                'token' => Str::random(40),
                'invited_by' => $request->user()->id,
                'expires_at' => now()->addDays(self::EXPIRY_DAYS),
                'accepted_at' => null,
            ],
        );

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return back()->with('status', 'Invitation sent to '.$invitation->email.'.');
    }

    public function destroy(Request $request, Invitation $invitation): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageMembers', $tenant);

        abort_unless($invitation->tenant_id === $tenant->id, 404);

        $invitation->delete();

        return back()->with('status', 'Invitation revoked.');
    }

    /** Landing page for an invitation link. */
    public function show(string $token): View
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        return view('invitations.show', [
            'invitation' => $invitation,
            'tenant' => $invitation->tenant,
        ]);
    }

    public function accept(Request $request, string $token, AcceptInvitation $action): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $action->accept($invitation, $request->user());

        return redirect()
            ->route('tenant.dashboard', ['tenant' => $invitation->tenant_id])
            ->with('status', 'Welcome to '.$invitation->tenant->name.'!');
    }
}
