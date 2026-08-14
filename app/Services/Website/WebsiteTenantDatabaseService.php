<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WebsiteTenantDatabaseService
{
    /**
     * Connect Laravel to a website's dedicated database.
     */
    public function connect(Website $website): void
    {
        $connection = $website->databaseConnection;

        if (!$connection) {
            throw new \RuntimeException(
                'No database connection is configured for this website.'
            );
        }

        Config::set('database.connections.website_tenant', [
            'driver' => 'mysql',
            'host' => $connection->host,
            'port' => $connection->port,
            'database' => $connection->database,
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'unix_socket' => config('database.connections.mysql.unix_socket'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('website_tenant');
        DB::reconnect('website_tenant');
    }

    /**
     * Disconnect the current website database connection.
     */
    public function disconnect(): void
    {
        DB::disconnect('website_tenant');
        DB::purge('website_tenant');
    }

    /**
     * Get the active website database connection.
     */
    public function connection()
    {
        return DB::connection('website_tenant');
    }
}
