<?php

namespace App\Services\Core\Installer\Contracts;

/**
 * ESUBIZ_CORE_UNIVERSAL_PRODUCT_INSTALLER_HANDLER_V1
 *
 * Product-specific handler contract used by the universal
 * OFF-SERVER Core Marketplace Installer.
 *
 * Supported product families:
 *
 * - core
 * - theme
 * - module
 * - addon
 * - bundle
 *
 * ESUBIZ_CORE_INSTALLATION_MODEL_V3
 *
 * CORE HAS A DIFFERENT FIRST-INSTALL ENTRY POINT:
 *
 * INITIAL OFF-SERVER CORE / WEBSITE INSTALLATION
 *
 * The user manually:
 *
 * 1. Uploads the Core/website ZIP to their server.
 * 2. Unzips the package.
 * 3. Opens the built-in Core installer.
 * 4. Enters the required installation information.
 * 5. Enters the Esubiz license.
 * 6. Core validates the license with Central Esubiz.
 * 7. Core installs/configures itself.
 * 8. The website becomes ready.
 *
 * Central Esubiz does NOT automatically download or install Core
 * for the first installation.
 *
 * CORE UPDATES
 *
 * After Core is installed, automatic updates use the same secure
 * Central package delivery/update infrastructure:
 *
 * Check -> Authorize -> Download -> Validate -> Stage ->
 * Backup/Rollback Point -> Update -> Keep Enabled
 *
 * OTHER CORE PRODUCTS
 *
 * Themes, Modules, Add-ons and Bundles support both:
 *
 * AUTOMATIC
 * Download -> Validate -> Unzip -> Install -> Enable
 *
 * MANUAL
 * Uploaded ZIP + License -> Validate -> Unzip -> Install -> Enable
 *
 * IMPORTANT:
 *
 * The universal installer owns the common installation pipeline:
 *
 * AUTOMATIC
 * Download -> Validate -> Unzip -> Install -> Enable
 *
 * MANUAL
 * Uploaded ZIP + License -> Validate -> Unzip -> Install -> Enable
 *
 * UPDATE
 * Download -> Validate -> Unzip -> Update -> Keep Enabled
 *
 * Individual handlers own only the product-specific work.
 *
 * ESUBIZ_CORE_PRODUCT_DESTINATION_OWNERSHIP_V2
 *
 * IMPORTANT DESTINATION RULE:
 *
 * The universal installer MUST NEVER decide a product's live
 * installation directory or registration location.
 *
 * Each product handler owns its correct Core destination and
 * installation semantics.
 *
 * Examples:
 *
 * - CoreHandler handles Core update deployment only after Core exists.
 *   Initial Core installation is performed by Core's built-in setup
 *   installer from an already uploaded/unzipped package.
 * - ThemeHandler installs/registers only in the Core Theme system.
 * - ModuleHandler installs/registers only in the Core Module system.
 * - AddonHandler installs/registers only in the Core Add-on system.
 * - BundleHandler resolves its contents and delegates every contained
 *   product to that product's own handler.
 *
 * A Module must never be installed into a Theme location, a Theme
 * must never be installed into a Module location, and so on.
 *
 * The handler also owns how that product is enabled after installation.
 *
 * There is NO separate product "activate" lifecycle in Esubiz.
 * Enabled means installed and active/usable.
 *
 * SaaS fulfilment does NOT use this installer.
 */
interface CoreProductInstallerHandler
{
    /**
     * Marketplace product type handled by this implementation.
     *
     * Examples:
     * core
     * theme
     * module
     * addon
     * bundle
     */
    public function productType(): string;

    /**
     * Validate the already-extracted package before any live
     * installation/change occurs.
     *
     * This is product-specific validation in addition to the
     * universal ZIP, checksum, manifest and license validation.
     *
     * Throw an exception when invalid.
     *
     * @param array<string,mixed> $manifest
     * @param array<string,mixed> $context
     */
    public function validate(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): void;

    /**
     * Install the product into Core.
     *
     * IMPORTANT FOR product_type=core:
     *
     * This method is NOT the initial Core setup installer.
     * Initial Core installation starts from a manually uploaded
     * and already-unzipped Core/website package and is handled by
     * Core's own built-in setup flow.
     *
     * CoreHandler is used by the installed Core for update deployment.
     *
     * For Theme, Module, Add-on and Bundle installation, the universal
     * installer has already:
     *
     * - validated entitlement/license;
     * - validated package identity;
     * - verified checksum where applicable;
     * - safely extracted into staging.
     *
     * This method must NOT enable the product.
     *
     * @param array<string,mixed> $manifest
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function install(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array;

    /**
     * Enable the successfully installed product.
     *
     * "Enable" is the Esubiz user-facing term.
     * There is no separate product activation step.
     *
     * @param array<string,mixed> $manifest
     * @param array<string,mixed> $installation
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function enable(
        array $manifest,
        array $installation,
        array $context = []
    ): array;

    /**
     * Update an existing installed product using a validated,
     * staged newer package.
     *
     * For product_type=core, this is the automatic Core update path.
     * It runs only after Core has already been installed.
     *
     * Existing enabled products should remain enabled after a
     * successful update.
     *
     * @param array<string,mixed> $manifest
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function update(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array;

    /**
     * Attempt to restore the last working installation when an
     * install/update fails after live changes have begun.
     *
     * Rollback behavior differs by product family, therefore
     * each handler owns its rollback implementation.
     *
     * @param array<string,mixed> $context
     */
    public function rollback(
        array $context = []
    ): void;

    /**
     * URL/path Core should send the user to after successful
     * installation or update.
     *
     * This may come from the validated package manifest.
     *
     * Examples:
     * /admin/themes
     * /admin/modules/example
     *
     * @param array<string,mixed> $manifest
     */
    public function startUrl(
        array $manifest
    ): ?string;
}
