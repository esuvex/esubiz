<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class WebsiteTenant extends SpatieTenant
{
    protected $table = 'website_database_connections';

    protected $fillable = [
        'website_id',
        'host',
        'port',
        'database',
        'status',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }
}
