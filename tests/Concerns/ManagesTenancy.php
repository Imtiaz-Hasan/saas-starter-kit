<?php

namespace Tests\Concerns;

use App\Actions\ProvisionTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Test helper for multi-database tenancy. Laravel auto-invokes setUp/tearDown
 * methods named after the trait. We freshly migrate the central database and drop
 * any tenant databases around each test, rather than using RefreshDatabase —
 * CREATE DATABASE is DDL that breaks transaction-based refreshing.
 */
trait ManagesTenancy
{
    protected function setUpManagesTenancy(): void
    {
        $this->artisan('migrate:fresh');
        $this->dropTenantDatabases();
    }

    protected function tearDownManagesTenancy(): void
    {
        $this->dropTenantDatabases();
    }

    protected function dropTenantDatabases(): void
    {
        $databases = DB::connection('central')->select(
            "SELECT schema_name AS name FROM information_schema.schemata WHERE schema_name LIKE 'tenant\\_%'"
        );

        foreach ($databases as $row) {
            DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$row->name}`");
        }
    }

    /** Create a verified user. */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /** Provision a new organization owned by the given (or a fresh) user. */
    protected function provisionTenant(?User $owner = null, string $name = 'Acme Inc'): Tenant
    {
        $owner ??= $this->createUser();

        return app(ProvisionTenant::class)->create($owner, $name);
    }
}
