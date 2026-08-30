<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Support\Str;
use RuntimeException;

/*
 * ESUBIZ_GENERIC_WEBSITE_PRODUCT_GRANT_DISPATCHER_V2
 *
 * Permanent Website Edit Admin grant dispatcher.
 *
 * Product UI/controller code does not change when a new product
 * family is introduced.
 *
 * Existing authoritative paths:
 *
 * credit_package -> AdminProductGrantService credit ledger
 * core_addon     -> CoreAddonMarketplaceFulfilmentService
 * core_bundle    -> CoreAddonMarketplaceFulfilmentService
 *
 * Pluggable product paths:
 *
 * theme          -> ThemeAdminGrantHandler
 * module         -> ModuleAdminGrantHandler
 * future_type    -> FutureTypeAdminGrantHandler
 *
 * A future product only needs its own fulfilment handler; Website
 * Edit and WebsiteController remain unchanged.
 */
class WebsiteProductGrantDispatcher
{
    public function grant(
        Website $website,
        string $productType,
        int $productId,
        int $adminId,
        int $quantity = 1
    ): array {
        $quantity = max(1, $quantity);

        $type = $this->canonicalType(
            $productType
        );

        if ($type === 'credit_package') {
            return app(
                AdminProductGrantService::class
            )->grantCreditPackage(
                $website,
                $productId,
                $adminId,
                $quantity
            );
        }

        if (
            in_array(
                $type,
                [
                    'core_addon',
                    'core_bundle',
                ],
                true
            )
        ) {
            return app(
                AdminProductGrantService::class
            )->grantCoreProduct(
                $website,
                $productId,
                $type,
                $adminId
            );
        }

        /*
         * Plug-and-play fulfilment convention.
         *
         * theme:
         * App\Services\Marketplace\AdminGrants\
         * ThemeAdminGrantHandler
         *
         * module:
         * App\Services\Marketplace\AdminGrants\
         * ModuleAdminGrantHandler
         *
         * Any future product follows the same naming contract.
         */
        $handlerClass =
            'App\\Services\\Marketplace\\AdminGrants\\'
            . Str::studly($type)
            . 'AdminGrantHandler';

        if (!class_exists($handlerClass)) {
            throw new RuntimeException(
                "No Admin grant fulfilment handler is registered "
                . "for product type [{$type}]."
            );
        }

        $handler = app(
            $handlerClass
        );

        if (!method_exists($handler, 'grant')) {
            throw new RuntimeException(
                "Admin grant handler [{$handlerClass}] "
                . "must provide grant()."
            );
        }

        return $handler->grant(
            $website,
            $productId,
            $adminId,
            $quantity
        );
    }


    private function canonicalType(
        string $productType
    ): string {
        $type =
            strtolower(
                trim($productType)
            );

        return match ($type) {
            'addon',
            'core-addon',
            'core_addon'
                => 'core_addon',

            'bundle',
            'core-bundle',
            'core_bundle'
                => 'core_bundle',

            'credit',
            'credits',
            'credit_package',
            'ai_credit',
            'ai_credits',
            'sms_credit',
            'sms_credits',
            'email_credit',
            'email_credits',
            'whatsapp_credit',
            'whatsapp_credits'
                => 'credit_package',

            'themes'
                => 'theme',

            'modules'
                => 'module',

            default =>
                $type,
        };
    }
}
