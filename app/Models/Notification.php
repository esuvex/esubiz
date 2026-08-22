<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'notification_channel_id',
        'notification_template_id',
        'user_id',
        'uuid',
        'reference',
        'recipient',
        'subject',
        'body',
        'notifiable_type',
        'notifiable_id',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'provider_reference',
        'provider_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'provider_response' => 'array',
    ];
}
