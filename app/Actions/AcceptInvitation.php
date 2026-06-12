<?php

namespace App\Actions;

use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns a pending invitation into a membership for the accepting user.
 */
class AcceptInvitation
{
    public function accept(Invitation $invitation, User $user): Membership
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation is no longer valid.',
            ]);
        }

        if (strcasecmp($invitation->email, $user->email) !== 0) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation was sent to a different email address.',
            ]);
        }

        return DB::connection('central')->transaction(function () use ($invitation, $user) {
            $membership = Membership::updateOrCreate(
                ['tenant_id' => $invitation->tenant_id, 'user_id' => $user->id],
                ['role' => $invitation->role->value],
            );

            $invitation->forceFill(['accepted_at' => now()])->save();
            $user->forceFill(['current_tenant_id' => $invitation->tenant_id])->save();

            return $membership;
        });
    }
}
