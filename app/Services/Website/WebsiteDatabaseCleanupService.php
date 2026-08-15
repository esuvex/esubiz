<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Models\WebsiteDatabaseConnection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WebsiteDatabaseCleanupService
{
    /**
     * Remove the dedicated tenant database belonging to a website.
     */
    public function cleanup(Website $website): void
    {
        $connection = WebsiteDatabaseConnection::query()
            ->where('website_id', $website->id)
            ->first();

        if (!$connection) {
            return;
        }

        $expectedDatabase = WebsiteDatabaseConnection::databaseNameFor($website);

        if ($connection->database !== $expectedDatabase) {
            throw new RuntimeException(
                'Tenant database name does not match the expected Esubiz database name.'
            );
        }

        $database = str_replace('`', '``', $connection->database);

        DB::connection('landlord')->statement(
            'DROP DATABASE IF EXISTS `' . $database . '`'
        );

        $connection->delete();
    }
}
