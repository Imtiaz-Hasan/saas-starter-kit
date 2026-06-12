<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant\Project;
use Tests\Concerns\ManagesTenancy;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use ManagesTenancy;

    public function test_data_created_in_one_tenant_is_invisible_to_another(): void
    {
        $acme = $this->provisionTenant(name: 'Acme');
        $globex = $this->provisionTenant($this->createUser(), 'Globex');

        $acme->run(fn () => Project::create(['name' => 'Acme confidential']));

        // Globex's database is physically separate — it sees nothing.
        $globex->run(function () {
            $this->assertSame(0, Project::count());
        });

        // Acme still sees exactly its own row.
        $acme->run(function () {
            $this->assertSame(1, Project::count());
            $this->assertSame('Acme confidential', Project::first()->name);
        });
    }

    public function test_owner_can_view_tenant_dashboard(): void
    {
        $owner = $this->createUser();
        $this->provisionTenant($owner, 'Acme');

        $this->actingAs($owner)->get('/acme/dashboard')->assertOk();
    }

    public function test_non_member_cannot_access_tenant_routes(): void
    {
        $this->provisionTenant(name: 'Acme');
        $outsider = $this->createUser();

        $this->actingAs($outsider)->get('/acme/dashboard')->assertForbidden();
    }
}
