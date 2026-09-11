<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreCentralConnection extends Model
{
    /**
     * ESUBIZ_OFF_SERVER_CENTRAL_CONNECTION_MODEL_V1
     *
     * This model exists inside Core.
     *
     * access_token is encrypted at rest using the
     * installation's APP_KEY.
     */
    protected $fillable = [
        'central_url',
        'website_id',
        'website_uuid',
        'installation_id',
        'installation_uuid',
        'api_application_id',
        'access_token',
        'token_type',
        'scopes',
        'active',
        'last_verified_at',
        'last_used_at',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'access_token' =>
            'encrypted',

        'scopes' =>
            'array',

        'active' =>
            'boolean',

        'last_verified_at' =>
            'datetime',

        'last_used_at' =>
            'datetime',
    ];
}
