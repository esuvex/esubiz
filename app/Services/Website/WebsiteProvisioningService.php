<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Services\Website\Recipes\RecipeService;
use RuntimeException;

class WebsiteProvisioningService
{
    protected RecipeService $recipeService;

    protected WebsiteDatabaseProvisioningService $databaseProvisioningService;

    protected TenantCoreInstallationService $tenantCoreInstallationService;

    protected WebsiteDatabaseCleanupService $databaseCleanupService;

    protected WebsiteTypeCompositionService $websiteTypeCompositionService;

    protected WebsiteMailboxService $websiteMailboxService;

    public function __construct(
        RecipeService $recipeService,
        WebsiteDatabaseProvisioningService $databaseProvisioningService,
        TenantCoreInstallationService $tenantCoreInstallationService,
        WebsiteDatabaseCleanupService $databaseCleanupService,
        WebsiteTypeCompositionService $websiteTypeCompositionService,
        WebsiteMailboxService $websiteMailboxService
    ) {
        $this->recipeService = $recipeService;
        $this->databaseProvisioningService = $databaseProvisioningService;
        $this->tenantCoreInstallationService = $tenantCoreInstallationService;
        $this->databaseCleanupService = $databaseCleanupService;
        $this->websiteTypeCompositionService = $websiteTypeCompositionService;
        $this->websiteMailboxService = $websiteMailboxService;
    }

    /**
     * Provision a newly created SaaS website.
     */
    public function provision(Website $website): void
    {
        /*
         * ESUBIZ_SAAS_WEBSITE_TYPE_COMPOSITION_PIPELINE_V3
         *
         * Website Type is resolved generically.
         *
         * No Business/Ecommerce/Hotel/School-specific provisioning
         * logic belongs in this service.
         */
        $websiteType = trim((string) ($website->type ?? ''));

        if ($websiteType === '') {
            throw new RuntimeException(
                'Website Type is required before SaaS provisioning can begin.'
            );
        }

        $wizardData = is_array($website->wizard_data)
            ? $website->wizard_data
            : [];

        $selectedTheme = $this->resolveSelectedTheme($wizardData);

        $profile = $this->recipeService->deploymentProfile(
            $websiteType,
            'saas',
            $selectedTheme
        );

        try {

            /*
             * ESUBIZ_REAL_DEPLOYMENT_PROGRESS_V2
             */
            $website->update([
                'status' => 'provisioning',
                'deployment_progress' => 10,
            ]);

            /*
            |----------------------------------------------------------------------
            | Dedicated Tenant Database
            |----------------------------------------------------------------------
            */

            $website->update([
                'deployment_progress' => 30,
            ]);

            $this->databaseProvisioningService->provision($website);

            $website->update([
                'deployment_progress' => 45,
            ]);

            /*
            |----------------------------------------------------------------------
            | Install Native Esubiz Core
            |----------------------------------------------------------------------
            */

            $website->update([
                'deployment_progress' => 60,
            ]);

            $this->tenantCoreInstallationService->install($website);

            $website->update([
                'deployment_progress' => 75,
            ]);

            /*
            |----------------------------------------------------------------------
            | Apply Website Type Composition
            |----------------------------------------------------------------------
            |
            | Native Core is now initialized.
            |
            | The Website Type specialization is applied here:
            |
            | - Modules
            | - Add-ons
            | - Add-on bundles
            | - Selected/default Theme
            |
            | The current legacy config recipes contain slugs only.
            | They intentionally remain non-deployable until the
            | Admin-managed deployment-profile resolver supplies
            | genuine package/staging information.
            |
            | Once that resolver is wired, this pipeline automatically
            | applies every future Website Type without type-specific code.
            |
            */

            if ($this->profileIsDeployable($profile)) {
                $this->websiteTypeCompositionService->apply(
                    $website,
                    $profile,
                    [
                        'deployment' => 'saas',
                        'provisioning_source' => 'website_type',
                    ]
                );
            }

            $website->update([
                'deployment_progress' => 90,
            ]);

            /*
            |----------------------------------------------------------------------
            | Website Ready
            |----------------------------------------------------------------------
            */

            $website->update([
                'status' => 'active',
                'deployment_progress' => 98,
            ]);

            /*
             * ESUBIZ_SAAS_MANAGED_MAIL_PROVISIONING_V2
             *
             * SaaS mailbox rule:
             *
             * johnstore.esubiz.com -> johnstore@esubiz.com
             *
             * DirectAdmin integration remains opt-in through Central
             * configuration. Until enabled, existing SaaS deployment
             * behavior remains unchanged.
             *
             * Once enabled, a temporary mail-server failure is recorded
             * and reported without destroying an otherwise successfully
             * deployed Core website.
             */
            if (
                $website->isSaas()
                && config(
                    'esubiz_mail.directadmin.enabled',
                    false
                )
            ) {
                try {
                    $this->websiteMailboxService
                        ->provisionSaas(
                            $website
                        );
                } catch (\Throwable $mailboxException) {
                    report(
                        $mailboxException
                    );
                }
            }

        } catch (\Throwable $e) {

            /*
            |----------------------------------------------------------------------
            | Provisioning Failure Cleanup
            |----------------------------------------------------------------------
            */

            try {
                $this->databaseCleanupService->cleanup($website);
            } catch (\Throwable $cleanupException) {
                report($cleanupException);
            }

            $website->update([
                'status' => 'failed',
                'deployment_progress' => 0,
            ]);

            throw $e;
        }
    }

    /**
     * Resolve whichever theme field the current/future wizard uses.
     *
     * This remains generic and does not know any Website Type names.
     */
    protected function resolveSelectedTheme(array $wizardData): ?string
    {
        foreach ([
            'theme_slug',
            'selected_theme',
            'theme',
        ] as $key) {
            if (!array_key_exists($key, $wizardData)) {
                continue;
            }

            $value = $wizardData[$key];

            if (is_array($value)) {
                $value = $value['slug']
                    ?? $value['product_slug']
                    ?? null;
            }

            $value = trim((string) ($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * A profile becomes deployable only after its product resolver has
     * attached genuine prepared package/staging paths.
     *
     * This prevents the transitional legacy slug-only recipes from
     * breaking live SaaS website creation.
     */
    protected function profileIsDeployable(array $profile): bool
    {
        $components = [];

        foreach (['modules', 'addons', 'bundles'] as $key) {
            foreach (($profile[$key] ?? []) as $item) {
                if (is_array($item)) {
                    $components[] = $item;
                }
            }
        }

        $themeSlug = trim((string) (
            $profile['selected_theme']
            ?? $profile['default_theme']
            ?? ''
        ));

        if ($themeSlug !== '') {
            foreach (($profile['themes'] ?? []) as $theme) {
                if (!is_array($theme)) {
                    continue;
                }

                $slug = trim((string) (
                    $theme['slug']
                    ?? $theme['product_slug']
                    ?? ''
                ));

                if ($slug === $themeSlug) {
                    $components[] = $theme;
                    break;
                }
            }
        }

        /*
         * A Website Type with genuinely no components is valid.
         * A Website Type containing components is deployable only
         * when every required component has a prepared staging path.
         */
        if ($components === []) {
            return true;
        }

        foreach ($components as $component) {
            $path = trim((string) (
                $component['extracted_path']
                ?? $component['staging_path']
                ?? ''
            ));

            if ($path === '') {
                return false;
            }
        }

        return true;
    }
}
