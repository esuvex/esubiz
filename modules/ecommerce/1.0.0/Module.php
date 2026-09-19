<?php

use App\Services\Core\Modules\Contracts\CoreModule;
use App\Services\Core\Modules\Registries\CoreModulePageRegistry;
use App\Services\Core\Modules\Registries\CoreModuleWidgetRegistry;

return new class implements CoreModule
{
    public function slug(): string
    {
        return 'ecommerce';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function register(): void
    {
        $this->registerPages();
        $this->registerWidgets();
    }

    public function boot(): void
    {
        /*
         * Module-owned classes are loaded only while this module is
         * enabled. Disabling Ecommerce therefore removes its runtime
         * functionality without deleting its files or business data.
         */
        spl_autoload_register(
            static function (string $class): void {
                $prefix = 'Esubiz\\Modules\\Ecommerce\\';

                if (!str_starts_with($class, $prefix)) {
                    return;
                }

                $relative = substr($class, strlen($prefix));

                $file = __DIR__
                    . '/'
                    . str_replace('\\', '/', $relative)
                    . '.php';

                if (is_file($file)) {
                    require_once $file;
                }
            }
        );

        app('view')->addNamespace(
            'ecommerce',
            __DIR__ . '/Resources/views'
        );

        if (is_dir(__DIR__ . '/Database/Migrations')) {
            app('migrator')->path(
                __DIR__ . '/Database/Migrations'
            );
        }

        $routes = __DIR__ . '/Routes/web.php';

        if (is_file($routes)) {
            require $routes;
        }
    }

    public function requiresCoreFeatures(): array
    {
        /*
         * Ecommerce integrates with Core capabilities when those
         * integrations are enabled for the module.
         *
         * Actual availability, entitlement and usage limits remain
         * authoritative in Core.
         */
        return [];
    }

    protected function registerPages(): void
    {
        $registry = app(CoreModulePageRegistry::class);

        foreach ($this->pages() as $key => $page) {
            $registry->register(
                'ecommerce',
                $key,
                $page
            );
        }
    }

    protected function registerWidgets(): void
    {
        $registry = app(CoreModuleWidgetRegistry::class);

        $widgets = [
            'product_grid' => [
                'label' => 'Product Grid',
                'icon' => '▦',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.product_grid',
                'defaults' => [
                    'heading' => 'Shop Products',
                    'limit' => 8,
                    'columns' => 4,
                    'category' => '',
                    'showPrice' => true,
                    'showButton' => true,
                ],
            ],

            'featured_products' => [
                'label' => 'Featured Products',
                'icon' => '★',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.featured_products',
                'defaults' => [
                    'heading' => 'Featured Products',
                    'limit' => 4,
                    'columns' => 4,
                    'showPrice' => true,
                    'showButton' => true,
                ],
            ],

            'product_categories' => [
                'label' => 'Product Categories',
                'icon' => '▤',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.product_categories',
                'defaults' => [
                    'heading' => 'Shop by Category',
                    'limit' => 8,
                    'columns' => 4,
                ],
            ],

            'product_details' => [
                'label' => 'Product Details',
                'icon' => '□',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.product_details',
                'defaults' => [
                    'showGallery' => true,
                    'showPrice' => true,
                    'showStock' => true,
                    'showQuantity' => true,
                    'showAddToCart' => true,
                ],
            ],

            'cart' => [
                'label' => 'Shopping Cart',
                'icon' => '🛒',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.cart',
                'defaults' => [
                    'heading' => 'Your Cart',
                    'showImages' => true,
                    'showQuantity' => true,
                    'showTotals' => true,
                ],
            ],

            'checkout' => [
                'label' => 'Checkout',
                'icon' => '✓',
                'module_name' => 'Ecommerce',
                'fallback_view' => 'ecommerce::widgets.checkout',
                'defaults' => [
                    'heading' => 'Checkout',
                    'showOrderSummary' => true,
                ],
            ],
        ];

        foreach ($widgets as $type => $definition) {
            $registry->register(
                'ecommerce',
                $type,
                $definition
            );
        }
    }

    protected function pages(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'url' => '/admin/ecommerce',
                'order' => 10,
                'permission' => 'ecommerce.view',
            ],

            'products' => [
                'label' => 'Products',
                'url' => '/admin/ecommerce/products',
                'order' => 20,
                'permission' => 'ecommerce.products.view',
            ],

            'categories' => [
                'label' => 'Categories',
                'url' => '/admin/ecommerce/categories',
                'order' => 30,
                'permission' => 'ecommerce.categories.view',
            ],

            'inventory' => [
                'label' => 'Inventory',
                'url' => '/admin/ecommerce/inventory',
                'order' => 40,
                'permission' => 'ecommerce.inventory.view',
            ],

            'collections' => [
                'label' => 'Collections',
                'url' => '/admin/ecommerce/collections',
                'order' => 50,
                'permission' => 'ecommerce.collections.view',
            ],

            'orders' => [
                'label' => 'Orders',
                'url' => '/admin/ecommerce/orders',
                'order' => 60,
                'permission' => 'ecommerce.orders.view',
            ],

            'customers' => [
                'label' => 'Customers',
                'url' => '/admin/ecommerce/customers',
                'order' => 70,
                'permission' => 'ecommerce.customers.view',
            ],

            'discounts' => [
                'label' => 'Coupons & Discounts',
                'url' => '/admin/ecommerce/discounts',
                'order' => 80,
                'permission' => 'ecommerce.discounts.view',
            ],

            'checkout_links' => [
                'label' => 'Checkout Links',
                'url' => '/admin/ecommerce/checkout-links',
                'order' => 90,
                'permission' => 'ecommerce.checkout-links.view',
            ],

            'shipping' => [
                'label' => 'Shipping & Delivery',
                'url' => '/admin/ecommerce/shipping',
                'order' => 100,
                'permission' => 'ecommerce.shipping.view',
            ],

            'returns' => [
                'label' => 'Returns & Refunds',
                'url' => '/admin/ecommerce/returns',
                'order' => 110,
                'permission' => 'ecommerce.returns.view',
            ],

            'reviews' => [
                'label' => 'Product Reviews',
                'url' => '/admin/ecommerce/reviews',
                'order' => 120,
                'permission' => 'ecommerce.reviews.view',
            ],

            'currencies' => [
                'label' => 'Currencies',
                'url' => '/admin/ecommerce/currencies',
                'order' => 130,
                'permission' => 'ecommerce.settings.view',
            ],

            'payments' => [
                'label' => 'Payments',
                'url' => '/admin/ecommerce/payments',
                'order' => 140,
                'permission' => 'ecommerce.settings.view',
            ],

            'settings' => [
                'label' => 'Ecommerce Settings',
                'url' => '/admin/ecommerce/settings',
                'order' => 150,
                'permission' => 'ecommerce.settings.manage',
            ],

            'reports' => [
                'label' => 'Sales Reports',
                'url' => '/admin/ecommerce/reports',
                'order' => 160,
                'permission' => 'ecommerce.reports.view',
            ],
        ];
    }
};
