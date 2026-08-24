<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'uuid',
        'name',
        'type',
        'currency',
        'available_balance',
        'pending_balance',
        'reserved_balance',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
