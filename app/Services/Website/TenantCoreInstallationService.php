<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantCoreInstallationService
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Install the mandatory Esubiz Core schema
     * into a website's dedicated tenant database.
     */
    public function install(Website $website): void
    {
        $connection = $website->databaseConnection;

        if (!$connection) {
            throw new RuntimeException(
                'Cannot install Core: no tenant database connection exists.'
            );
        }

        $this->tenantDatabaseService->connect($website);

        try {

            $this->createMigrationTable();

            $this->runTenantCoreMigrations($website);

            $this->seedTenantCore();

        } finally {

            $this->tenantDatabaseService->disconnect();
        }
    }

    /**
     * Create the tenant migration repository.
     */
    protected function createMigrationTable(): void
    {
        $connection = DB::connection('website_tenant');

        if (!$connection->getSchemaBuilder()->hasTable('migrations')) {
            $connection->getSchemaBuilder()->create(
                'migrations',
                function ($table) {
                    $table->bigIncrements('id');
                    $table->string('migration');
                    $table->integer('batch');
                }
            );
        }
    }

    /**
     * Run only Core tenant migrations.
     */
    protected function runTenantCoreMigrations(
        Website $website
    ): void {

        $exitCode = Artisan::call(
            'migrate',
            [
                '--database' => 'website_tenant',
                '--path' => 'database/migrations/tenant/core',
                '--force' => true,
            ]
        );

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Esubiz Core tenant migrations failed: ' .
                Artisan::output()
            );
        }
    }

    /**
     * Seed the mandatory Core defaults.
     */
    protected function seedTenantCore(): void
    {
        $exitCode = Artisan::call(
            'db:seed',
            [
                '--database' => 'website_tenant',
                '--class' => 'Database\\Seeders\\TenantCoreSeeder',
                '--force' => true,
            ]
        );

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Esubiz Core tenant seeding failed: ' .
                Artisan::output()
            );
        }
    }
}
