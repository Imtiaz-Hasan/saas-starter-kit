<?php

use App\Http\Controllers\Central\BillingController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\InvitationController;
use App\Http\Controllers\Central\MemberController;
use App\Http\Controllers\Central\OrganizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| Loaded by bootstrap/app.php with the `web` middleware group. Tenancy is NOT
| initialized here, so every model touched by these routes (User, Tenant,
| Membership, Invitation, Cashier subscriptions) reads from the central database.
|
| Keeping billing here is deliberate: invoking Cashier while a tenant connection
| is active is a known stancl/tenancy pitfall, so all billing stays central.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Central hub — redirects to the user's current organization, or to the
    // "create organization" screen if they don't belong to one yet.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Organizations (tenants)
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::put('/organizations/{tenant}/switch', [OrganizationController::class, 'switch'])->name('organizations.switch');
    Route::delete('/organizations/{tenant}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

    // Members of the current organization
    Route::get('/organization/members', [MemberController::class, 'index'])->name('members.index');
    Route::put('/organization/members/{membership}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/organization/members/{membership}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Invitations issued for the current organization
    Route::post('/organization/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/organization/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    // Billing for the current organization (Cashier; central context only)
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

// Accepting an invitation only requires authentication (a freshly registered,
// not-yet-verified user can still join the org they were invited to).
Route::middleware('auth')->group(function () {
    Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
});
