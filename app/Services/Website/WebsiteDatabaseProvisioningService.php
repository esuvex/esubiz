<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Models\WebsiteDatabaseConnection;
use Illuminate\Support\Facades\DB;

class WebsiteDatabaseProvisioningService
{
    /**
     * Create the dedicated database for a website.
     */
    public function provision(Website $website): WebsiteDatabaseConnection
    {
        $databaseName = WebsiteDatabaseConnection::databaseNameFor($website);

        DB::connection('landlord')->statement(
            'CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );

        return WebsiteDatabaseConnection::create([
            'website_id' => $website->id,
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => $databaseName,
            'status' => 'active',
        ]);
    }
}
