<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Concerns\ResolvesCurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;

/**
 * Billing for the current organization. The Tenant is the Cashier Billable, and
 * these routes run in the CENTRAL context (tenancy is never initialized here) —
 * invoking Cashier inside a tenant connection is a known stancl/tenancy pitfall.
 */
class BillingController extends Controller
{
    use ResolvesCurrentTenant;

    public function index(Request $request): View
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageBilling', $tenant);

        return view('billing.index', [
            'tenant' => $tenant,
            'plans' => config('plans.plans'),
            'subscription' => $tenant->subscription('default'),
            'onTrial' => $tenant->onTrial('default'),
        ]);
    }

    /** Start Stripe Checkout for the chosen plan. */
    public function checkout(Request $request, string $plan): Checkout|RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageBilling', $tenant);

        $config = config("plans.plans.$plan");

        abort_unless($config && ! empty($config['price_id']), 404, 'Unknown or unpriced plan.');

        return $tenant->newSubscription('default', $config['price_id'])
            ->trialDays($config['trial_days'] ?? 0)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.index').'?checkout=success',
                'cancel_url' => route('billing.index'),
                'metadata' => ['plan' => $plan, 'tenant_id' => $tenant->id],
            ]);
    }

    /** Redirect to the Stripe-hosted billing portal to manage the subscription. */
    public function portal(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant($request);
        $this->authorize('manageBilling', $tenant);

        return $tenant->redirectToBillingPortal(route('billing.index'));
    }
}
