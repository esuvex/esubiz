<?php

namespace App\Support\CentralApi;

use InvalidArgumentException;

/**
 * ================================================================
 * ESUBIZ CENTRAL SERVICE SCOPE REGISTRY
 * ================================================================
 *
 * One canonical list of protected Central Esubiz capabilities.
 *
 * This prevents AI, SMS, Email, WhatsApp, Gift Card, Marketplace,
 * Payments and future services from inventing separate scope names.
 */
class CentralServiceScopeRegistry
{
    public const WEBSITE_IDENTITY =
        'website.identity';

    public const LICENSE_VERIFY =
        'license.verify';

    public const MARKETPLACE_READ =
        'marketplace.read';

    public const MARKETPLACE_CHECKOUT =
        'marketplace.checkout';

    public const MARKETPLACE_ENTITLEMENTS =
        'marketplace.entitlements';

    public const PACKAGES_DOWNLOAD =
        'packages.download';

    public const CREDITS_READ =
        'credits.read';

    public const AI_USE =
        'ai.use';

    public const SMS_USE =
        'sms.use';

    public const EMAIL_USE =
        'email.use';

    public const WHATSAPP_USE =
        'whatsapp.use';

    public const SOCIAL_USE =
        'social.use';

    public const GIFTCARD_VALIDATE =
        'giftcard.validate';

    public const GIFTCARD_REDEEM =
        'giftcard.redeem';

    public const PAYMENTS_CONNECT =
        'payments.connect';

    public const UPDATES_READ =
        'updates.read';


    public static function all(): array
    {
        return [
            static::WEBSITE_IDENTITY,
            static::LICENSE_VERIFY,
            static::MARKETPLACE_READ,
            static::MARKETPLACE_CHECKOUT,
            static::MARKETPLACE_ENTITLEMENTS,
            static::PACKAGES_DOWNLOAD,
            static::CREDITS_READ,
            static::AI_USE,
            static::SMS_USE,
            static::EMAIL_USE,
            static::WHATSAPP_USE,
            static::SOCIAL_USE,
            static::GIFTCARD_VALIDATE,
            static::GIFTCARD_REDEEM,
            static::PAYMENTS_CONNECT,
            static::UPDATES_READ,
        ];
    }


    public static function has(
        string $scope
    ): bool {

        return in_array(
            trim($scope),
            static::all(),
            true
        );
    }


    public static function assertKnown(
        string $scope
    ): string {

        $scope =
            trim($scope);


        if (!static::has($scope)) {
            throw new InvalidArgumentException(
                "Unknown Esubiz Central service scope [{$scope}]."
            );
        }


        return $scope;
    }
}
