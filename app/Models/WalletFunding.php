<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletFunding extends Model
{
    protected $fillable = [
        'wallet_id',
        'workspace_id',
        'user_id',
        'uuid',
        'reference',
        'amount',
        'fee',
        'net_amount',
        'currency',
        'method',
        'gateway_reference',
        'payload',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payload' => 'array',
    ];
}
