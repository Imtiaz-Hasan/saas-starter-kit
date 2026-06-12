<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-tenant key/value setting, stored in the TENANT database. Prefer reading
 * and writing through App\Support\TenantSettings, which adds a cache layer.
 */
class TenantSetting extends Model
{
    protected $fillable = ['key', 'value'];
}
