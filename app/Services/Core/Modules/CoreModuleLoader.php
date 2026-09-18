<?php

namespace App\Services\Core\Modules;

use App\Services\Core\Installer\CoreInstalledProductRegistry;
use App\Services\Core\Modules\Contracts\CoreModule;
use RuntimeException;

/**
 * ESUBIZ_CORE_MODULE_LOADER_V1
 *
 * Loads installed + enabled modules into the current Core runtime.
 *
 * Disabled modules are never booted, so their pages, menus, widgets,
 * routes and runtime integrations disappear automatically while their
 * stored data remains untouched.
 *
 * Every portable module exposes:
 *
 * modules/<slug>/Module.php
 *
 * That file must return a CoreModule instance.
 */
class CoreModuleLoader
{
    public function __construct(
        protected CoreInstalledProductRegistry $installedProducts,
        protected CoreModuleRegistry $modules
    ) {
    }

    public function load(): void
    {
        foreach ($this->installedProducts->all('module') as $installed) {
            if (($installed['is_enabled'] ?? false) !== true) {
                continue;
            }

            $this->loadInstalledModule($installed);
        }

        $this->modules->registerIntegrations();
        $this->modules->bootModules();
    }

    protected function loadInstalledModule(array $installed): void
    {
        $slug = trim((string) (
            $installed['product_slug'] ?? ''
        ));

        if ($slug === '') {
            throw new RuntimeException(
                'Enabled Core module has no product slug.'
            );
        }

        $installPath = trim((string) (
            $installed['install_path'] ?? ''
        ));

        if ($installPath === '') {
            $installPath = base_path('modules/' . $slug);
        }

        $entryFile = rtrim(
            $installPath,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR . 'Module.php';

        if (!is_file($entryFile)) {
            throw new RuntimeException(
                "Enabled module [{$slug}] is missing Module.php."
            );
        }

        $module = require $entryFile;

        if (!$module instanceof CoreModule) {
            throw new RuntimeException(
                "Module [{$slug}] must return an instance of "
                . CoreModule::class
                . ' from Module.php.'
            );
        }

        if ($module->slug() !== $slug) {
            throw new RuntimeException(
                "Module runtime slug [{$module->slug()}] does not match installed slug [{$slug}]."
            );
        }

        $this->modules->register($module);
    }
}
