<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Pin to the central connection so auth + membership lookups keep hitting the
    // central database even after tenancy swaps the default connection.
    use CentralConnection;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ----------------------------------------------------------------------
    // Organization (tenant) relationships — all on the central connection.
    // ----------------------------------------------------------------------

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /** All organizations this user belongs to. */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'memberships', 'user_id', 'tenant_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** The organization the user is currently working inside (may be null). */
    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    // ----------------------------------------------------------------------
    // Access-control helpers. These are the single source of truth used by the
    // EnsureMemberOfTenant / EnsureRole middleware and the policies.
    // ----------------------------------------------------------------------

    public function belongsToTenant(Tenant $tenant): bool
    {
        return $this->memberships()->where('tenant_id', $tenant->id)->exists();
    }

    /** The user's role within the given tenant, or null if not a member. */
    public function roleIn(Tenant $tenant): ?Role
    {
        $membership = $this->memberships()->where('tenant_id', $tenant->id)->first();

        return $membership?->role;
    }

    public function isOwnerOf(Tenant $tenant): bool
    {
        return $this->roleIn($tenant) === Role::Owner;
    }

    /** True if the user's role in the tenant is at least the given role. */
    public function hasTenantRole(Tenant $tenant, Role $role): bool
    {
        return $this->roleIn($tenant)?->atLeast($role) ?? false;
    }
}
