<?php

namespace App\Actions;

use App\Enums\Role;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates a new organization (tenant) and provisions its dedicated database.
 *
 * Flow:
 *   1. Derive a unique, URL-safe slug from the organization name. The slug is the
 *      tenant's primary key, its URL prefix, and the basis of its DB name.
 *   2. Create the Tenant row. This fires stancl's TenantCreated event which
 *      (synchronously by default) runs CreateDatabase -> MigrateDatabase, so the
 *      tenant database exists and is migrated by the time create() returns.
 *   3. Inside a central-DB transaction, attach the owner Membership and point the
 *      owner at the new organization.
 *
 * Why the tenant row is created OUTSIDE the transaction: CREATE DATABASE is DDL,
 * which implicitly commits the open transaction in MySQL and cannot be rolled
 * back. Wrapping it would corrupt the transaction state. The owner attachment in
 * step 3 is the part that genuinely benefits from atomicity, so only it is wrapped.
 * If the tenant is later deleted, TenantDeleted -> DeleteDatabase drops its database.
 */
class ProvisionTenant
{
    /**
     * Slugs reserved for central (non-tenant) routes — a tenant may not claim one,
     * otherwise its URL prefix would shadow a platform route.
     */
    private const RESERVED = [
        'login', 'register', 'logout', 'dashboard', 'organizations', 'billing',
        'profile', 'stripe', 'verify-email', 'email', 'password', 'forgot-password',
        'reset-password', 'confirm-password', 'invitations', 'up', 'api', 'admin',
    ];

    public function create(User $owner, string $name): Tenant
    {
        $slug = $this->uniqueSlug($name);

        // Step 2 — create the tenant (provisions + migrates its database via the
        // TenantCreated pipeline). DDL, so this must run outside a transaction.
        $tenant = Tenant::create([
            'id' => $slug,
            'name' => $name,
            'plan' => config('plans.default', 'free'),
        ]);

        // Step 3 — attach the owner atomically.
        DB::connection('central')->transaction(function () use ($owner, $tenant) {
            Membership::create([
                'tenant_id' => $tenant->id,
                'user_id' => $owner->id,
                'role' => Role::Owner->value,
            ]);

            $owner->forceFill(['current_tenant_id' => $tenant->id])->save();
        });

        return $tenant;
    }

    /**
     * Build a unique slug from the name, guarding against reserved words and
     * collisions with existing tenants.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            throw ValidationException::withMessages([
                'name' => 'The organization name must contain at least one letter or number.',
            ]);
        }

        $slug = $base;
        $i = 1;

        while (in_array($slug, self::RESERVED, true) || Tenant::whereKey($slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
