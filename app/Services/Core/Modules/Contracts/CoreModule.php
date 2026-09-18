<?php

namespace App\Services\Core\Modules\Contracts;

/**
 * ESUBIZ_CORE_MODULE_CONTRACT_V1
 *
 * Universal contract for every Esubiz Core module.
 *
 * Module commercial/catalog configuration such as:
 * - name
 * - description
 * - pricing
 * - compatible Website Types
 * - sidebar parent menu name
 * - marketplace information
 *
 * is Central-owned configuration.
 *
 * The installed module supplies its Core integrations:
 * - pages/features
 * - submenu navigation
 * - Page Builder widgets
 * - permissions
 * - Core feature requirements
 * - routes/services/runtime boot logic
 */
interface CoreModule
{
    /**
     * Stable module slug used by installation and runtime registries.
     */
    public function slug(): string;

    /**
     * Module version.
     */
    public function version(): string;

    /**
     * Register module-owned features, pages, permissions,
     * navigation and widgets with existing Core registries.
     *
     * Registration must not duplicate Core-owned functionality.
     */
    public function register(): void;

    /**
     * Boot runtime integrations after registration.
     *
     * Called only while the module is installed and enabled.
     */
    public function boot(): void;

    /**
     * Core feature keys required by this module.
     *
     * Example:
     * [
     *     'payment_gateways.online',
     *     'forms',
     * ]
     */
    public function requiresCoreFeatures(): array;
}
