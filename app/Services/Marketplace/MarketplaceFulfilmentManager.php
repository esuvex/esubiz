<?php

namespace App\Services\Marketplace;

use RuntimeException;

class MarketplaceFulfilmentManager
{
    protected array $handlers = [

            /*
             * OFF_SERVER_LICENSE_MARKETPLACE_HANDLER
             *
             * License-only Marketplace products issue a pending
             * domain-unlocked license after successful payment.
             */
            'license' =>
                \App\Services\Marketplace\Handlers\OffServerLicenseFulfilmentHandler::class,

            'off_server_license' =>
                \App\Services\Marketplace\Handlers\OffServerLicenseFulfilmentHandler::class,


            /*
             * Generic Esubiz credit products.
             *
             * All credit systems use one central fulfilment path.
             * The package itself determines whether the purchased
             * balance is AI, SMS, Email, WhatsApp or a future
             * registered credit type.
             */
            'credit' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,

            'credit_package' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,

            'ai_credits' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,

            'sms_credits' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,

            'email_credits' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,

            'whatsapp_credits' =>
                \App\Services\Marketplace\Handlers\CreditPackageFulfilmentHandler::class,
];

    public function register(string $productType, object $handler): void
    {
        $this->handlers[$productType] = $handler;
    }

    public function fulfil(object $order, object $listing): array
    {
        $handler = $this->handlers[$listing->product_type] ?? null;

        /*
         * Registered handlers may be concrete objects or class strings.
         * Resolve class strings through Laravel so constructor
         * dependencies such as WebsiteCreditService are injected.
         */
        if (
            is_string($handler)
            && class_exists($handler)
        ) {
            $handler = app($handler);

            $this->handlers[
                $listing->product_type
            ] = $handler;
        }

        if (!$handler) {
            $class = 'App\\Services\\Marketplace\\Handlers\\'
                . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $listing->product_type)))
                . 'FulfilmentHandler';

            if (class_exists($class)) {
                $handler = app($class);
                $this->register($listing->product_type, $handler);
            }
        }

        if (!$handler || !method_exists($handler, 'fulfil')) {
            throw new RuntimeException(
                "No marketplace fulfilment handler registered for product type [{$listing->product_type}]."
            );
        }

        return $handler->fulfil($order, $listing);
    }
}
