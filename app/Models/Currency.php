<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'symbol',
        'country',
        'decimal_separator',
        'thousand_separator',
        'decimal_places',
        'is_base',
        'exchange_rate',
        'is_active',
        'is_crypto',
    ];

    protected function casts(): array
    {
        return [
            'decimal_places' => 'integer',
            'is_base' => 'boolean',
            'exchange_rate' => 'decimal:8',
            'is_active' => 'boolean',
            'is_crypto' => 'boolean',
        ];
    }
}
