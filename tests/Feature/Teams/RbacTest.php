<?php

namespace Tests\Feature\Teams;

use App\Enums\Role;
use App\Models\Membership;
use Tests\Concerns\ManagesTenancy;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use ManagesTenancy;

    public function test_role_hierarchy(): void
    {
        $this->assertTrue(Role::Owner->atLeast(Role::Admin));
        $this->assertTrue(Role::Admin->atLeast(Role::Member));
        $this->assertFalse(Role::Member->atLeast(Role::Admin));
    }

    public function test_owner_can_view_billing_but_member_cannot(): void
    {
        $owner = $this->createUser();
        $tenant = $this->provisionTenant($owner, 'Acme');

        $this->actingAs($owner)->get(route('billing.index'))->assertOk();

        $member = $this->createUser();
        Membership::create(['tenant_id' => $tenant->id, 'user_id' => $member->id, 'role' => Role::Member->value]);
        $member->forceFill(['current_tenant_id' => $tenant->id])->save();

        $this->actingAs($member)->get(route('billing.index'))->assertForbidden();
    }

    public function test_admin_can_view_members_page(): void
    {
        $owner = $this->createUser();
        $tenant = $this->provisionTenant($owner, 'Acme');

        $admin = $this->createUser();
        Membership::create(['tenant_id' => $tenant->id, 'user_id' => $admin->id, 'role' => Role::Admin->value]);
        $admin->forceFill(['current_tenant_id' => $tenant->id])->save();

        $this->actingAs($admin)->get(route('members.index'))->assertOk();
    }
}
