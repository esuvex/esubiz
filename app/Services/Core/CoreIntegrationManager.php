<?php

namespace App\Services\Core;

use App\Models\ModuleCapability;
use App\Models\ModuleInstallation;

class CoreIntegrationManager
{
    /**
     * Determine whether a Core capability is enabled
     * for a module installation.
     *
     * Core integrations are enabled by default.
     * A module installation may explicitly disable one.
     */
    public function isEnabled(
        ModuleInstallation $installation,
        string $capability
    ): bool {
        $configuration = $installation->configuration ?? [];

        $integrations = $configuration['core_integrations'] ?? [];

        return ($integrations[$capability] ?? true) === true;
    }

    /**
     * Enable or disable a Core capability for a module.
     */
    public function setEnabled(
        ModuleInstallation $installation,
        string $capability,
        bool $enabled
    ): void {
        $configuration = $installation->configuration ?? [];

        $configuration['core_integrations'] ??= [];

        $configuration['core_integrations'][$capability] = $enabled;

        $installation->update([
            'configuration' => $configuration,
        ]);
    }

    /**
     * Return configured Core integrations.
     */
    public function integrations(
        ModuleInstallation $installation
    ): array {
        return $installation->configuration['core_integrations'] ?? [];
    }

    /**
     * Return Core capabilities available to the module.
     *
     * Only capabilities explicitly registered as Core
     * integrations are returned.
     */
    public function availableCoreCapabilities(
        ModuleInstallation $installation
    ) {
        return ModuleCapability::query()
            ->where('module_id', $installation->module_id)
            ->where('type', 'integration')
            ->get();
    }
}
