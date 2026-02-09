<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Pricing
    |--------------------------------------------------------------------------
    |
    | All amounts are in pesewas (1 GHS = 100 pesewas).
    | Annual prices include a 15% discount.
    |
    */

    'plans' => [
        'new' => [
            'name' => 'New',
            'monthly' => 10000,  // ₵100
            'annual' => 102000,  // ₵1,020 (₵85/mo)
        ],
        'basic' => [
            'name' => 'Basic',
            'monthly' => 18000,  // ₵180
            'annual' => 183600,  // ₵1,836 (₵153/mo)
        ],
        'premium' => [
            'name' => 'Premium',
            'monthly' => 55000,  // ₵550
            'annual' => 561000,  // ₵5,610 (₵468/mo)
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'monthly' => 180000,  // ₵1,800
            'annual' => 1836000, // ₵18,360 (₵1,530/mo)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    'currency' => 'GHS',
    'currency_symbol' => '₵',
];
