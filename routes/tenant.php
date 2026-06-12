<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\ProjectController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantSettingsController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes run INSIDE a tenant's context. The {tenant} path segment is
| resolved by InitializeTenancyByPath against the tenant's primary key (its
| slug), and stancl swaps the default database connection to tenant_<slug> for
| the duration of the request. From here on, models like Project transparently
| read/write the tenant's own database.
|
| Middleware order matters: authenticate the user and initialize tenancy first,
| THEN confirm membership (tenant.member). The member check also registers a URL
| default so route('tenant.*') links work without passing {tenant} explicitly.
|
*/

Route::middleware([
    'web',
    'auth',
    'verified',
    InitializeTenancyByPath::class,
    'tenant.member',
])->prefix('/{tenant}')->name('tenant.')->group(function () {
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/settings', [TenantSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [TenantSettingsController::class, 'update'])->name('settings.update');
});
