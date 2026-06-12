<?php

namespace App\Support;

use App\Models\Tenant\TenantSetting;

/**
 * Thin accessor over the per-tenant settings table with a small per-request memo
 * so repeated reads in one request don't re-hit the database.
 *
 * Must only be called while tenancy is initialized (i.e. from tenant routes).
 * Settings physically live in the tenant database, so they are isolated by
 * construction. We deliberately avoid the Cache facade here: stancl's optional
 * cache-tenancy bootstrapper relies on cache tags, which the default cache stores
 * don't support — a request-scoped memo keeps this working everywhere.
 */
class TenantSettings
{
    /** @var array<string, array<string, mixed>> keyed by tenant id then setting key */
    private static array $memo = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        $tenantId = tenant('id');

        if (! array_key_exists($key, self::$memo[$tenantId] ?? [])) {
            self::$memo[$tenantId][$key] = TenantSetting::where('key', $key)->value('value');
        }

        return self::$memo[$tenantId][$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        TenantSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$memo[tenant('id')][$key] = $value;
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        return TenantSetting::pluck('value', 'key')->all();
    }
}
