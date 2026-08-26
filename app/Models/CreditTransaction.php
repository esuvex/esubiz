<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'website_id',
        'user_id',
        'workspace_id',
        'credit_type',
        'uuid',
        'request_key',
        'reference',
        'direction',
        'type',
        'credits',
        'balance_before',
        'balance_after',
        'source_type',
        'source_id',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'credits' => 'decimal:6',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
        'metadata' => 'array',
    ];


    public function website(): BelongsTo
    {
        return $this->belongsTo(
            Website::class
        );
    }
}
