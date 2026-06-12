<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant\Project;
use Tests\Concerns\ManagesTenancy;
use Tests\TestCase;

class TenantProvisioningTest extends TestCase
{
    use ManagesTenancy;

    public function test_provisioning_creates_central_record_and_owner_membership(): void
    {
        $tenant = $this->provisionTenant(name: 'Acme Inc');

        $this->assertSame('acme-inc', $tenant->id);
        $this->assertDatabaseHas('tenants', ['id' => 'acme-inc', 'name' => 'Acme Inc'], 'central');
        $this->assertDatabaseHas('memberships', ['tenant_id' => 'acme-inc', 'role' => 'owner'], 'central');
    }

    public function test_provisioning_creates_and_migrates_a_tenant_database(): void
    {
        $tenant = $this->provisionTenant(name: 'Acme Inc');

        // The tenant database exists and its migrations ran: we can use the model.
        $tenant->run(function () {
            $this->assertSame(0, Project::count());
            Project::create(['name' => 'First project']);
            $this->assertSame(1, Project::count());
        });
    }

    public function test_slugs_are_made_unique(): void
    {
        $first = $this->provisionTenant(name: 'Acme');
        $second = $this->provisionTenant($this->createUser(), 'Acme');

        $this->assertSame('acme', $first->id);
        $this->assertSame('acme-2', $second->id);
    }
}
