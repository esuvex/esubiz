<?php

namespace App\Services\Core\Installer;

use App\Services\Marketplace\MarketplaceProductDeploymentService;
use RuntimeException;

/**
 * ESUBIZ_CORE_MARKETPLACE_DEPLOYMENT_EXECUTOR_V1
 *
 * Executes a centrally-authorized Marketplace deployment inside the
 * target Esubiz Core.
 *
 * Responsibilities:
 * - claim the central deployment instruction;
 * - enforce target/deployment context;
 * - pass entitlement/license/package context to the Core installer;
 * - install or update the validated/extracted package;
 * - report completion or failure centrally.
 *
 * Package download, checksum verification, license validation and safe
 * extraction happen before execute() receives $extractedPath.
 */
class CoreMarketplaceDeploymentExecutor
{
    public function __construct(
        protected MarketplaceProductDeploymentService $deployments,
        protected CoreMarketplaceProductDeploymentService $installer
    ) {
    }

    public function execute(
        int $deploymentId,
        string $extractedPath,
        array $manifest,
        array $runtimeContext = []
    ): array {
        if ($deploymentId <= 0) {
            throw new RuntimeException(
                'A valid Marketplace deployment ID is required.'
            );
        }

        if (!is_dir($extractedPath)) {
            throw new RuntimeException(
                'Validated Marketplace package staging directory does not exist.'
            );
        }

        $deployment = $this->deployments->claim(
            $deploymentId,
            $runtimeContext
        );

        if (($deployment['status'] ?? null) === 'completed') {
            return [
                'completed' => true,
                'already_completed' => true,
                'deployment' => $deployment,
                'result' => $deployment['result'] ?? [],
            ];
        }

        try {
            $productType = strtolower(
                trim(
                    (string) (
                        $deployment['product_type']
                        ?? ''
                    )
                )
            );

            $action = strtolower(
                trim(
                    (string) (
                        $deployment['action']
                        ?? 'install'
                    )
                )
            );

            $deploymentType = strtolower(
                trim(
                    (string) (
                        $deployment['deployment_type']
                        ?? ''
                    )
                )
            );

            if (!in_array(
                $deploymentType,
                ['saas', 'off_server'],
                true
            )) {
                throw new RuntimeException(
                    "Unsupported deployment type [{$deploymentType}]."
                );
            }

            $this->assertTargetContext(
                $deployment,
                $runtimeContext
            );

            $context = array_merge(
                $deployment['context'] ?? [],
                $runtimeContext,
                [
                    'deployment_id' =>
                        (int) $deployment['id'],

                    'deployment_uuid' =>
                        $deployment['uuid']
                        ?? null,

                    'deployment' =>
                        $deploymentType,

                    'deployment_type' =>
                        $deploymentType,

                    'website_id' =>
                        $deployment['website_id']
                        ?? null,

                    'entitlement_id' =>
                        (int) (
                            $deployment['entitlement_id']
                            ?? 0
                        ),

                    'license_id' =>
                        $deployment['license_id']
                        ?? null,

                    'catalog_product_id' =>
                        $deployment['catalog_product_id']
                        ?? null,

                    'marketplace_listing_id' =>
                        $deployment['marketplace_listing_id']
                        ?? null,

                    'checksum_sha256' =>
                        $deployment['checksum_sha256']
                        ?? null,

                    'product_slug' =>
                        $deployment['product_slug']
                        ?? (
                            $manifest['slug']
                            ?? $manifest['product_slug']
                            ?? null
                        ),

                    'product_version' =>
                        $deployment['product_version']
                        ?? (
                            $manifest['version']
                            ?? $manifest['product_version']
                            ?? null
                        ),

                    'source' =>
                        'marketplace_deployment',
                ]
            );

            if ($action === 'install') {
                $result = $this->installer->deploy(
                    $productType,
                    $extractedPath,
                    $manifest,
                    $context
                );
            } elseif ($action === 'update') {
                $result = $this->installer->update(
                    $productType,
                    $extractedPath,
                    $manifest,
                    $context
                );
            } else {
                throw new RuntimeException(
                    "Unsupported Marketplace deployment action [{$action}]."
                );
            }

            $completed = $this->deployments->complete(
                $deploymentId,
                $result
            );

            return [
                'completed' => true,
                'already_completed' => false,
                'deployment' => $completed,
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $this->deployments->fail(
                $deploymentId,
                $e
            );

            throw $e;
        }
    }

    protected function assertTargetContext(
        array $deployment,
        array $runtimeContext
    ): void {
        $deploymentType =
            $deployment['deployment_type']
            ?? null;

        if ($deploymentType === 'saas') {
            $expectedWebsiteId =
                (int) (
                    $deployment['website_id']
                    ?? 0
                );

            $runtimeWebsiteId =
                (int) (
                    $runtimeContext['website_id']
                    ?? $expectedWebsiteId
                );

            if (
                $expectedWebsiteId > 0
                && $runtimeWebsiteId !== $expectedWebsiteId
            ) {
                throw new RuntimeException(
                    'Marketplace deployment does not belong to this SaaS website.'
                );
            }
        }

        if ($deploymentType === 'off_server') {
            $licenseId =
                (int) (
                    $deployment['license_id']
                    ?? 0
                );

            if ($licenseId <= 0) {
                throw new RuntimeException(
                    'Off-server Marketplace deployment has no authorized license.'
                );
            }
        }
    }
}
