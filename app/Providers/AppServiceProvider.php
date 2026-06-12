<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The Tenant (organization) is the Cashier Billable model, not the User.
        // All billing data therefore lives on the central `tenants` table.
        Cashier::useCustomerModel(Tenant::class);

        // Owner short-circuit: a tenant's owner is allowed every ability checked
        // against that tenant, so individual policy methods only describe the
        // admin/member boundary. Returning null lets the policy decide.
        Gate::before(function ($user, string $ability, array $arguments = []) {
            $model = $arguments[0] ?? null;

            if ($model instanceof Tenant && $user->isOwnerOf($model)) {
                return true;
            }

            return null;
        });
    }
}
