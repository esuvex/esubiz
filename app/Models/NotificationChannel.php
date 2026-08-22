<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationChannel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'type',
        'provider',
        'credentials',
        'is_enabled',
        'is_default',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
    ];
}
