<?php

namespace App\Services\Core\Installer;

use RuntimeException;

/**
 * ESUBIZ_CORE_MARKETPLACE_PRODUCT_DEPLOYMENT_V1
 *
 * Universal bridge between a validated Marketplace product package
 * and the Esubiz Core product installer.
 *
 * Commercial fulfilment remains responsible for:
 * - payment/order state;
 * - entitlement creation;
 * - licensing.
 *
 * This service is responsible only for deploying an already-authorized,
 * validated and safely-extracted Core-installable package.
 *
 * Supported Core product families:
 * - theme
 * - module
 * - addon
 * - bundle
 *
 * Mobile apps are intentionally excluded. They belong to the Esubiz
 * App Builder/compiler/release pipeline.
 */
class CoreMarketplaceProductDeploymentService
{
    public function __construct(
        protected CoreProductInstallerManager $installers
    ) {
    }

    public function deploy(
        string $productType,
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $type = $this->normalizeType($productType);

        $this->assertDeployable($type);
        $this->assertAuthorizationContext($context);

        $context = array_merge(
            $context,
            [
                'product_type' => $type,
                'source' =>
                    $context['source']
                    ?? 'marketplace',
            ]
        );

        $result = $this->installers->installAndEnable(
            $type,
            $extractedPath,
            $manifest,
            $context
        );

        return [
            'product_type' => $type,
            'deployment' =>
                $context['deployment']
                ?? $context['deployment_type']
                ?? null,

            'website_id' =>
                $context['website_id']
                ?? null,

            'entitlement_id' =>
                $context['entitlement_id']
                ?? null,

            'license_id' =>
                $context['license_id']
                ?? null,

            'installed' => true,
            'enabled' => true,
            'result' => $result,
        ];
    }

    public function update(
        string $productType,
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $type = $this->normalizeType($productType);

        $this->assertDeployable($type);
        $this->assertAuthorizationContext($context);

        $context = array_merge(
            $context,
            [
                'product_type' => $type,
                'source' =>
                    $context['source']
                    ?? 'marketplace_update',
            ]
        );

        $result = $this->installers->update(
            $type,
            $extractedPath,
            $manifest,
            $context
        );

        return [
            'product_type' => $type,
            'deployment' =>
                $context['deployment']
                ?? $context['deployment_type']
                ?? null,

            'website_id' =>
                $context['website_id']
                ?? null,

            'entitlement_id' =>
                $context['entitlement_id']
                ?? null,

            'license_id' =>
                $context['license_id']
                ?? null,

            'updated' => true,
            'result' => $result,
        ];
    }

    public function supports(
        string $productType
    ): bool {
        $type = $this->normalizeType($productType);

        return in_array(
            $type,
            [
                'theme',
                'module',
                'addon',
                'bundle',
            ],
            true
        ) && $this->installers->supports($type);
    }

    protected function assertDeployable(
        string $productType
    ): void {
        if (!$this->supports($productType)) {
            throw new RuntimeException(
                "Marketplace product type [{$productType}] is not deployable through the Core installer."
            );
        }
    }

    protected function assertAuthorizationContext(
        array $context
    ): void {
        $deployment = strtolower(
            trim(
                (string) (
                    $context['deployment']
                    ?? $context['deployment_type']
                    ?? ''
                )
            )
        );

        if (!in_array(
            $deployment,
            ['saas', 'off_server'],
            true
        )) {
            throw new RuntimeException(
                'Core Marketplace deployment requires deployment=saas or off_server.'
            );
        }

        /*
         * Every deployable Core product belongs to one explicit website.
         * This applies equally to SaaS and licensed off-server Core.
         */
        $websiteId =
            (int) (
                $context['website_id']
                ?? 0
            );

        if ($websiteId <= 0) {
            throw new RuntimeException(
                'Core Marketplace deployment requires an authorized website.'
            );
        }

        $entitlementId =
            (int) (
                $context['entitlement_id']
                ?? 0
            );

        if ($entitlementId <= 0) {
            throw new RuntimeException(
                'Core Marketplace deployment requires an authorized entitlement.'
            );
        }

        /*
         * Off-server products are centrally licensed.
         * SaaS entitlement remains centrally authoritative and can be
         * website-scoped without requiring a manual license entry.
         */
        if ($deployment === 'off_server') {
            $licenseId =
                (int) (
                    $context['license_id']
                    ?? 0
                );

            if ($licenseId <= 0) {
                throw new RuntimeException(
                    'Off-server Core Marketplace deployment requires an authorized license.'
                );
            }
        }
    }

    protected function normalizeType(
        string $productType
    ): string {
        return strtolower(
            trim(
                str_replace(
                    '-',
                    '_',
                    $productType
                )
            )
        );
    }
}
