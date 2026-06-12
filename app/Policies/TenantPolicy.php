<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;

/**
 * Authorization for organization-level actions. Auto-discovered by Laravel for
 * the Tenant model. Owners bypass all of these via the Gate::before hook
 * registered in AppServiceProvider, so the checks below describe the ADMIN/MEMBER
 * boundary.
 */
class TenantPolicy
{
    /** Any member may view the organization. */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->belongsToTenant($tenant);
    }

    /** Admins (and owners) may invite, remove and re-role members. */
    public function manageMembers(User $user, Tenant $tenant): bool
    {
        return $user->hasTenantRole($tenant, Role::Admin);
    }

    /** Admins (and owners) may edit organization settings. */
    public function manageSettings(User $user, Tenant $tenant): bool
    {
        return $user->hasTenantRole($tenant, Role::Admin);
    }

    /** Only the owner manages billing. */
    public function manageBilling(User $user, Tenant $tenant): bool
    {
        return $user->isOwnerOf($tenant);
    }

    /** Only the owner may delete the organization. */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isOwnerOf($tenant);
    }
}
