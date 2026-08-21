<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central Esubiz Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Gateway adapters are registered here. Product modules and checkout
    | controllers must never communicate with a payment provider directly.
    |
    */

    'gateways' => [

        'nowpayments' => App\Services\Payment\Gateways\NOWPaymentsGateway::class,
        'paypal' => App\Services\Payment\Gateways\PayPalGateway::class,
        'flutterwave' => App\Services\Payment\Gateways\FlutterwaveGateway::class,

        'paystack' => App\Services\Payment\Gateways\PaystackGateway::class,

        // 'paystack' => App\Services\Payment\Gateways\PaystackGateway::class,
        // 'flutterwave' => App\Services\Payment\Gateways\FlutterwaveGateway::class,

    ],

];
