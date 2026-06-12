<?php

namespace Tests\Feature\Billing;

use Tests\Concerns\ManagesTenancy;
use Tests\TestCase;

/**
 * These tests exercise the billing wiring WITHOUT calling Stripe: a tenant with no
 * Stripe keys is not subscribed, and checkout for an unpriced/unknown plan 404s
 * before any network call. Full checkout requires real Stripe test keys.
 */
class SubscriptionTest extends TestCase
{
    use ManagesTenancy;

    public function test_tenant_is_billable_and_starts_unsubscribed(): void
    {
        $tenant = $this->provisionTenant(name: 'Acme');

        $this->assertFalse($tenant->subscribed('default'));
        $this->assertNull($tenant->subscription('default'));
    }

    public function test_checkout_404s_for_the_free_plan(): void
    {
        $owner = $this->createUser();
        $this->provisionTenant($owner, 'Acme');

        // The free plan has no Stripe price id, so checkout is not available.
        $this->actingAs($owner)
            ->post(route('billing.checkout', ['plan' => 'free']))
            ->assertNotFound();
    }

    public function test_checkout_404s_for_an_unknown_plan(): void
    {
        $owner = $this->createUser();
        $this->provisionTenant($owner, 'Acme');

        $this->actingAs($owner)
            ->post(route('billing.checkout', ['plan' => 'does-not-exist']))
            ->assertNotFound();
    }
}
