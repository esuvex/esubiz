<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'package_key',
        'package_version',
        'description',
        'icon',
        'image',
        'is_active',
        'show_in_user_wizard',
        'show_in_developer_wizard',
        'wizard_settings',
        'saas_price',
        'saas_billing_period',
        'saas_billing_interval',
        'off_server_price',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_user_wizard' => 'boolean',
        'show_in_developer_wizard' => 'boolean',
        'wizard_settings' => 'array',
        'saas_price' => 'decimal:2',
        'saas_billing_period' => 'integer',
        'off_server_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];
}
