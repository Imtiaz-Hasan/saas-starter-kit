<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Demo "feature" model living in the TENANT database.
 *
 * Note there is NO connection, no tenant_id column, and no global scope here.
 * Isolation is physical: once stancl initializes tenancy, the default database
 * connection points at tenant_<slug>, so every query against this model hits
 * that tenant's database and no other. That is the whole point of the multi-DB
 * approach — isolation you cannot forget to apply.
 */
class Project extends Model
{
    protected $fillable = ['name', 'description', 'status'];
}
