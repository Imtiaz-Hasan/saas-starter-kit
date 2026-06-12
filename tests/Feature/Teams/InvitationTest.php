<?php

namespace Tests\Feature\Teams;

use App\Enums\Role;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ManagesTenancy;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use ManagesTenancy;

    public function test_admin_can_invite_and_invitee_can_accept(): void
    {
        Mail::fake();

        $owner = $this->createUser();
        $tenant = $this->provisionTenant($owner, 'Acme');

        $this->actingAs($owner)
            ->post(route('invitations.store'), ['email' => 'new@example.com', 'role' => 'member'])
            ->assertRedirect();

        $this->assertDatabaseHas('invitations', [
            'tenant_id' => 'acme',
            'email' => 'new@example.com',
        ], 'central');
        Mail::assertSent(InvitationMail::class);

        $invitation = Invitation::first();
        $invitee = $this->createUser(['email' => 'new@example.com']);

        $this->actingAs($invitee)
            ->post(route('invitations.accept', ['token' => $invitation->token]))
            ->assertRedirect();

        $this->assertTrue($invitee->fresh()->belongsToTenant($tenant->fresh()));
    }

    public function test_members_cannot_invite(): void
    {
        $tenant = $this->provisionTenant(name: 'Acme');

        $member = $this->createUser();
        Membership::create(['tenant_id' => $tenant->id, 'user_id' => $member->id, 'role' => Role::Member->value]);
        $member->forceFill(['current_tenant_id' => $tenant->id])->save();

        $this->actingAs($member)
            ->post(route('invitations.store'), ['email' => 'x@example.com', 'role' => 'member'])
            ->assertForbidden();
    }

    public function test_cannot_invite_an_existing_member(): void
    {
        $owner = $this->createUser(['email' => 'owner@example.com']);
        $this->provisionTenant($owner, 'Acme');

        $this->actingAs($owner)
            ->post(route('invitations.store'), ['email' => 'owner@example.com', 'role' => 'member'])
            ->assertSessionHasErrors('email');
    }
}
