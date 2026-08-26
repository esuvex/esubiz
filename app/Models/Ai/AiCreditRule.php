<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiCreditRule extends Model
{
    protected $fillable = [
        'key',
        'label',
        'unit',
        'credits_per_unit',
        'minimum_credits',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'credits_per_unit' => 'decimal:6',
        'minimum_credits' => 'decimal:6',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];
}
