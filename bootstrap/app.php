<?php

use App\Http\Middleware\EnsureMemberOfTenant;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Central (non-tenant) application routes: organization management,
            // members, invitations and billing. Loaded with the web middleware
            // group; tenancy is deliberately NOT initialized here. Tenant routes
            // live in routes/tenant.php and are mapped by TenancyServiceProvider.
            Route::middleware('web')->group(base_path('routes/central.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.member' => EnsureMemberOfTenant::class,
            'tenant.role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
