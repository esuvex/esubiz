<?php

namespace App\Services\Marketplace\Settings;

use Illuminate\Support\Facades\Route;

class MarketplaceProductRegistry
{
    private const PRODUCTS = [
        'theme' => [
            'label' => 'Themes',
            'developer_build' => true,
            'developer_publish' => true,
            'developer_resell' => false,
        ],
        'module' => [
            'label' => 'Modules',
            'developer_build' => true,
            'developer_publish' => true,
            'developer_resell' => false,
        ],
        'addon' => [
            'label' => 'Add-ons',
            'developer_build' => true,
            'developer_publish' => true,
            'developer_resell' => false,
        ],
        'bundle' => [
            'label' => 'Bundles',
            'developer_build' => true,
            'developer_publish' => true,
            'developer_resell' => false,
        ],
        'website_type' => [
            'label' => 'Off-server Website Types',
            'developer_build' => true,
            'developer_publish' => true,
            'developer_resell' => false,
        ],

        'subscription' => [
            'label' => 'SaaS Subscription Plans',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'commission' => [
            'label' => 'SaaS Commission Plans',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'hybrid' => [
            'label' => 'SaaS Hybrid Plans',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'ai_credits' => [
            'label' => 'AI Credits',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'sms_credits' => [
            'label' => 'SMS Credits',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'email_credits' => [
            'label' => 'Email Credits',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'whatsapp_credits' => [
            'label' => 'WhatsApp Credits',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'app_builder' => [
            'label' => 'App Builder',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'esubiz_watermark' => [
            'label' => 'Esubiz Link Watermark',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'domain' => [
            'label' => 'Domains',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'hosting' => [
            'label' => 'Hosting',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
        'email' => [
            'label' => 'Emails',
            'developer_build' => false,
            'developer_publish' => false,
            'developer_resell' => true,
        ],
    ];

    public function all(): array
    {
        return self::PRODUCTS;
    }

    public function types(): array
    {
        return array_keys(self::PRODUCTS);
    }

    public function label(string $type): string
    {
        return self::PRODUCTS[$type]['label']
            ?? str($type)
                ->replace('_', ' ')
                ->title()
                ->toString();
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, self::PRODUCTS);
    }

    public function capability(
        string $type,
        string $capability
    ): bool {
        return (bool) (
            self::PRODUCTS[$type][$capability] ?? false
        );
    }

    public function canDeveloperBuild(string $type): bool
    {
        return $this->capability(
            $type,
            'developer_build'
        );
    }

    public function canDeveloperPublish(string $type): bool
    {
        return $this->capability(
            $type,
            'developer_publish'
        );
    }

    public function canDeveloperResell(string $type): bool
    {
        return $this->capability(
            $type,
            'developer_resell'
        );
    }

    public function developerBuildTypes(): array
    {
        return array_values(array_filter(
            $this->types(),
            fn (string $type) =>
                $this->canDeveloperBuild($type)
        ));
    }

    public function developerPublishTypes(): array
    {
        return array_values(array_filter(
            $this->types(),
            fn (string $type) =>
                $this->canDeveloperPublish($type)
        ));
    }

    public function developerResellTypes(): array
    {
        return array_values(array_filter(
            $this->types(),
            fn (string $type) =>
                $this->canDeveloperResell($type)
        ));
    }

    public function developerSellerTypes(): array
    {
        return array_values(array_filter(
            $this->types(),
            fn (string $type) =>
                $this->canDeveloperPublish($type)
                || $this->canDeveloperResell($type)
        ));
    }

    public function adminUrl(
        string $type,
        ?string $routeName = null
    ): ?string {
        if (!$routeName || !Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    public function isImplemented(
        ?string $routeName = null
    ): bool {
        return $routeName !== null
            && Route::has($routeName);
    }
}
