<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductStaging extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'uuid',
        'staging_key',
        'website_id',
        'workspace_id',
        'user_id',
        'marketplace_order_id',
        'marketplace_listing_id',
        'product_type',
        'product_id',
        'product_name',
        'product_version',
        'deployment_type',
        'staging_type',
        'status',
        'package_path',
        'checksum_sha256',
        'activation_area',
        'staged_at',
        'activated_at',
        'error_message',
        'metadata',
    ];


    protected $casts = [
        'metadata' =>
            'array',

        'staged_at' =>
            'datetime',

        'activated_at' =>
            'datetime',
    ];


    public function website(): BelongsTo
    {
        return $this->belongsTo(
            Website::class
        );
    }
}
