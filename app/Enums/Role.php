<?php

namespace App\Enums;

/**
 * A member's role within a tenant (organization).
 *
 * Stored as a string on the `memberships` pivot. Kept as a tiny backed enum on
 * purpose — there is no external permission package. Coarse gating is done with
 * the EnsureRole middleware; fine-grained checks live in the policies, with an
 * owner short-circuit registered via Gate::before in AppServiceProvider.
 */
enum Role: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    /** Human-readable label for the UI. */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    /**
     * Privilege rank, higher = more access. Used to compare roles, e.g. "is this
     * user at least an admin?" => $role->atLeast(Role::Admin).
     */
    public function rank(): int
    {
        return match ($this) {
            self::Owner => 100,
            self::Admin => 50,
            self::Member => 10,
        };
    }

    public function atLeast(Role $other): bool
    {
        return $this->rank() >= $other->rank();
    }

    /**
     * Roles that may be granted when inviting a member. "Owner" is intentionally
     * excluded — ownership transfers are a separate, deliberate action.
     */
    public static function assignable(): array
    {
        return [self::Admin, self::Member];
    }
}
