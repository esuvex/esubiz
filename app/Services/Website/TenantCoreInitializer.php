<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TenantCoreInitializer
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Initialize Esubiz Core data inside a tenant database.
     */
    public function initialize(Website $website): void
    {
        $this->tenantDatabaseService->connect($website);

        try {
            $db = $this->tenantDatabaseService->connection();

            $this->initializeInstallation($db);

            $this->initializeSettings($db, $website);

            $this->initializeAdministrator($db, $website);

            $this->initializeResources($db);

            $this->initializeLicense($db);

            $this->initializeDefaultLandingPage($db, $website);

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

    protected function initializeInstallation($db): void
    {
        $db->table('core_installations')->updateOrInsert(
            [
                'core_name' => 'core',
            ],
            [
                'core_version' => config('esubiz_core.version', '1.0.0'),
                'status' => 'installed',
                'installed_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function initializeSettings($db, Website $website): void
    {
        $settings = [
            'website_name' => $website->name,
            'website_type' => $website->type,
            'website_slug' => $website->slug,
            'subdomain' => $website->subdomain,
            'domain' => $website->domain,
            'theme' => config('esubiz_core.defaults.theme'),
        ];

        foreach ($settings as $key => $value) {
            $db->table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => is_array($value)
                        ? json_encode($value)
                        : $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    protected function initializeAdministrator($db, Website $website): void
    {
        if (!$website->admin_email) {
            return;
        }

        $db->table('site_users')->updateOrInsert(
            [
                'email' => $website->admin_email,
            ],
            [
                'name' => $website->admin_name ?: $website->name,
                'phone' => $website->wizard_data['admin_phone'] ?? null,
                'password' => $website->admin_password
                    ?: bcrypt(Str::random(32)),
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function initializeResources($db): void
    {
        $resources = config('esubiz_core.resources', []);

        foreach ($resources as $resource => $definition) {

            $limit = $definition['limit'] ?? null;
            $unit = $definition['unit'] ?? null;

            $db->table('resource_entitlements')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'base_limit' => $limit,
                    'addon_limit' => 0,
                    'is_unlimited' => false,
                    'unit' => $unit,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $db->table('resource_usage')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'used' => 0,
                    'limit' => $limit,
                    'is_unlimited' => false,
                    'unit' => $unit,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        foreach (config('esubiz_core.unlimited', []) as $resource) {

            $db->table('resource_entitlements')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'base_limit' => null,
                    'addon_limit' => 0,
                    'is_unlimited' => true,
                    'unit' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $db->table('resource_usage')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'used' => 0,
                    'limit' => null,
                    'is_unlimited' => true,
                    'unit' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    protected function initializeLicense($db): void
    {
        $db->table('core_licenses')->updateOrInsert(
            [
                'product_type' => 'core',
                'product_name' => 'core',
            ],
            [
                'license_key' => 'CORE-' . strtoupper(Str::random(24)),
                'product_version' => config('esubiz_core.version', '1.0.0'),
                'status' => 'active',
                'issued_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $db->table('license_assignments')->updateOrInsert(
            [
                'product_type' => 'core',
                'product_name' => 'core',
            ],
            [
                'license_key' => 'CORE-' . strtoupper(Str::random(24)),
                'product_version' => config('esubiz_core.version', '1.0.0'),
                'role' => 'core',
                'status' => 'active',
                'issued_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function initializeDefaultLandingPage(
        $db,
        Website $website
    ): void {
        $pageId = $db->table('pages')->insertGetId([
            'title' => $website->name,
            'slug' => 'home',
            'status' => 'published',
            'content' => '',
            'is_homepage' => true,
            'settings' => json_encode([]),
            'seo' => json_encode([]),
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $db->table('page_builder_documents')->insert([
            'page_id' => $pageId,
            'content' => json_encode([
                'type' => 'core-default',
                'version' => '1.0.0',
                'sections' => [],
            ]),
            'builder_version' => '1.0.0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
