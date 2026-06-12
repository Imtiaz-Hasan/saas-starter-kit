<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Support\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Per-tenant settings, stored in the tenant database via App\Support\TenantSettings.
 * Editing is gated to admins/owners through the TenantPolicy::manageSettings ability.
 */
class TenantSettingsController extends Controller
{
    public function edit(): View
    {
        $this->authorize('manageSettings', tenant());

        return view('tenant.settings.edit', [
            'tagline' => TenantSettings::get('tagline', ''),
            'weeklyDigest' => (bool) TenantSettings::get('weekly_digest', false),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manageSettings', tenant());

        $validated = $request->validate([
            'tagline' => ['nullable', 'string', 'max:255'],
            'weekly_digest' => ['nullable', 'boolean'],
        ]);

        TenantSettings::set('tagline', $validated['tagline'] ?? '');
        TenantSettings::set('weekly_digest', $request->boolean('weekly_digest'));

        return back()->with('status', 'Settings saved.');
    }
}
