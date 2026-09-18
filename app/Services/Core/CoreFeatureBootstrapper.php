<?php

namespace App\Services\Core;

/**
 * ESUBIZ_CORE_FEATURE_BOOTSTRAPPER_V1
 *
 * Registers Core-owned features that actually exist in this Core build.
 *
 * IMPORTANT:
 *
 * CoreFeatureRegistry may know about many possible features from many
 * sources. That does not make those features available.
 *
 * This bootstrapper registers only established built-in Core features.
 *
 * Add-ons, modules, bundles, themes, products and future site functions
 * should register themselves through CoreFeatureRegistry when their own
 * integration is booted, with an appropriate availability resolver.
 *
 * No SaaS/off-server branching belongs here.
 */
class CoreFeatureBootstrapper
{
    public function __construct(
        protected CoreFeatureRegistry $features
    ) {
    }

    public function boot(): void
    {
        /*
         * ESUBIZ_CORE_ADMIN_NAV_SITE_MANAGEMENT_V1
         *
         * Structured representation of the existing Core sidebar:
         *
         * Site Management
         *   - Pages
         *   - Media
         *   - Menus
         *   - Forms
         *   - Users
         *   - QR Code
         *   - Add-ons
         *   - Themes
         *   - Modules
         *
         * This does not render or relocate the sidebar.
         */
        app(\App\Services\Core\CoreAdminNavigationRegistry::class)
            ->register(
                'site_management',
                [
                    'label' => 'Site Management',
                    'order' => 100,
                    'children' => [
                        'pages' => [
                            'label' => 'Pages',
                            'order' => 10,
                        ],
                        'media' => [
                            'label' => 'Media',
                            'order' => 20,
                        ],
                        'menus' => [
                            'label' => 'Menus',
                            'order' => 30,
                        ],
                        'forms' => [
                            'label' => 'Forms',
                            'order' => 40,
                        ],
                        'users' => [
                            'label' => 'Users',
                            'order' => 50,
                        ],
                        'qr_code' => [
                            'label' => 'QR Code',
                            'order' => 60,
                        ],
                        'addons' => [
                            'label' => 'Add-ons',
                            'order' => 70,
                        ],
                        'themes' => [
                            'label' => 'Themes',
                            'order' => 80,
                        ],
                        'modules' => [
                            'label' => 'Modules',
                            'order' => 90,
                        ],
                    ],
                ]
            );
        $this->registerDashboard();
        $this->registerUsers();
        $this->registerRoles();
        $this->registerSettings();
        $this->registerForms();
        $this->registerSubmissions();
        $this->registerPaymentGateways();
        $this->registerPartners();
    }

    protected function registerDashboard(): void
    {
        $this->features->register([
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Dashboard',
            ],

            'navigation' => [
                'label' => 'Dashboard',
                'url' => '/admin',
                'permission' => 'dashboard.view',
                'order' => 10,
            ],

            'dashboard' => [
                'permission' => 'dashboard.view',
            ],
        ]);
    }

    protected function registerUsers(): void
    {
        $this->features->registerCrud(
            key: 'users',
            label: 'Users',
            url: '/admin/users',
            sourceType: 'core',
            sourceKey: null,
            extraPermissions: [],
            navigation: [
                'order' => 200,
            ],
            availability: true
        );
    }

    protected function registerRoles(): void
    {
        $this->features->registerCrud(
            key: 'roles',
            label: 'Roles & Permissions',
            url: '/admin/users/roles',
            sourceType: 'core',
            sourceKey: null,
            extraPermissions: [],
            navigation: [
                'order' => 210,
            ],
            availability: true
        );
    }

    protected function registerSettings(): void
    {
        $this->features->register([
            'key' => 'settings',
            'label' => 'Settings',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Settings',
                'edit' => 'Edit Settings',
            ],

            'navigation' => [
                'label' => 'Settings',
                'url' => '/admin/settings',
                'permission' => 'settings.view',
                'order' => 900,
            ],

            'dashboard' => [
                'permission' => 'settings.view',
            ],
        ]);
    }

    protected function registerForms(): void
    {
        $this->features->registerCrud(
            key: 'forms',
            label: 'Forms',
            url: '/admin/forms',
            sourceType: 'core',
            sourceKey: null,
            extraPermissions: [],
            navigation: [
                'order' => 400,
            ],
            availability: true
        );
    }

    protected function registerSubmissions(): void
    {
        $this->features->register([
            'key' => 'submissions',
            'label' => 'Submissions',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Submissions',
                'edit' => 'Edit Submissions',
                'delete' => 'Delete Submissions',
            ],

            'navigation' => [
                'label' => 'Submissions',
                'url' => '/admin/forms/submissions',
                'permission' => 'submissions.view',
                'order' => 410,
            ],

            'dashboard' => [
                'permission' => 'submissions.view',
            ],
        ]);
    }

    /**
     * Payment gateway administration is a built-in Core surface.
     *
     * URLs are intentionally not declared here until the canonical
     * gateway controllers/routes exist. The sidebar can expose the
     * menu structure without inventing destinations.
     */
    protected function registerPaymentGateways(): void
    {
        $this->features->register([
            'key' => 'payment_gateways.offline',
            'label' => 'Offline Payment Gateways',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Offline Payment Gateways',
                'edit' => 'Manage Offline Payment Gateways',
            ],
        ]);

        $this->features->register([
            'key' => 'payment_gateways.online',
            'label' => 'Online Payment Gateways',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Online Payment Gateways',
                'edit' => 'Manage Online Payment Gateways',
            ],
        ]);
    }

    protected function registerPartners(): void
    {
        /*
         * Partners / Investors is an established Core system role/
         * capability foundation, but its management/dashboard UI is
         * still being built.
         *
         * We therefore expose its permissions to RBAC without inventing
         * a management URL that does not yet exist.
         */

        $this->features->register([
            'key' => 'partners.dashboard',
            'label' => 'Partner / Investor Dashboard',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'view' => 'View Partner Dashboard',
            ],

            'dashboard' => [
                'permission' => 'partners.dashboard.view',
            ],
        ]);

        $this->features->register([
            'key' => 'partners',
            'label' => 'Partners / Investors',
            'source_type' => 'core',

            'availability' => true,

            'permissions' => [
                'manage' => 'Manage Partner Investments',
            ],
        ]);
    }
}
