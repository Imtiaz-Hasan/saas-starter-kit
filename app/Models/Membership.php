<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * The pivot record linking a user to a tenant, carrying their Role. Central DB.
 */
class Membership extends Model
{
    use CentralConnection;

    protected $fillable = ['tenant_id', 'user_id', 'role'];

    protected $casts = [
        'role' => Role::class,
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
