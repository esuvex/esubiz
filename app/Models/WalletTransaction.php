<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'workspace_id',
        'user_id',
        'uuid',
        'reference',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'source_type',
        'source_id',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];
}
