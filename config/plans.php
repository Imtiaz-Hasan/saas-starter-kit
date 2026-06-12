<?php

/*
|--------------------------------------------------------------------------
| Subscription Plans
|--------------------------------------------------------------------------
|
| Plans presented on the billing page and used by the BillingController to
| start Stripe Checkout. Price IDs come from your Stripe dashboard via .env —
| never hard-code real price IDs here. The "free" plan has no Stripe price and
| is the default for every newly provisioned organization.
|
*/

return [

    'default' => 'free',

    'plans' => [

        'free' => [
            'name' => 'Free',
            'price' => 0,
            'price_id' => null,
            'trial_days' => 0,
            'features' => [
                'Up to 3 team members',
                '5 projects',
                'Community support',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price' => 29,
            'price_id' => env('STRIPE_PRICE_PRO'),
            'trial_days' => 14,
            'features' => [
                'Up to 25 team members',
                'Unlimited projects',
                'Email support',
                '14-day free trial',
            ],
        ],

        'business' => [
            'name' => 'Business',
            'price' => 99,
            'price_id' => env('STRIPE_PRICE_BUSINESS'),
            'trial_days' => 14,
            'features' => [
                'Unlimited team members',
                'Unlimited projects',
                'Priority support',
                '14-day free trial',
            ],
        ],

    ],

];
