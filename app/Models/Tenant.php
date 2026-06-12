<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * A Tenant IS an organization in this starter kit (one organization = one tenant
 * = one database). It lives on the CENTRAL connection and is the Cashier Billable
 * entity, so all billing data is stored here, never inside a tenant database.
 *
 * The primary key is the human-readable slug (e.g. "acme"), which also names the
 * tenant database (tenant_acme) and appears in tenant URLs (/acme/dashboard).
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    // HasDatabase provides the database() manager required by TenantWithDatabase;
    // HasDomains enables optional domain/subdomain identification. Billable adds
    // Cashier. The base class already brings the central connection + data column.
    use HasDatabase, HasDomains, Billable;

    // The primary key is the slug string, not an auto-incrementing integer.
    // stancl's GeneratesIds trait decides incrementing/keyType based on whether a
    // UUID generator is bound; since we disabled the generator (id = slug), we
    // override these methods directly so the string key is never cast to an int.
    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function shouldGenerateId(): bool
    {
        return false;
    }

    /**
     * Columns stored as REAL columns rather than inside the virtual `data` JSON.
     * stancl's VirtualColumn trait moves any attribute NOT listed here into `data`;
     * Cashier needs stripe_id et al. to be real, queryable columns, so they must
     * appear in this list.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'plan',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    /** Members of this organization, with their role on the pivot. */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships', 'tenant_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'tenant_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'tenant_id');
    }

    /** The owner of the organization (the user who created it). */
    public function owner(): ?User
    {
        return $this->users()->wherePivot('role', Role::Owner->value)->first();
    }

    /** Cashier: use the owner's email when creating the Stripe customer. */
    public function stripeEmail(): ?string
    {
        return $this->owner()?->email;
    }

    /** Cashier: human-friendly name on the Stripe customer. */
    public function stripeName(): ?string
    {
        return $this->name;
    }

    /** The plan definition (from config/plans.php) this tenant is currently on. */
    public function planConfig(): array
    {
        return config('plans.plans.'.$this->plan, config('plans.plans.free'));
    }
}
