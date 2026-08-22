<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'notification_channel_id',
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'event',
        'is_enabled',
        'is_default',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
    ];
}
