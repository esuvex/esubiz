<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrencyPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'scope_type',
        'owner_id',
        'priceable_type',
        'priceable_id',
        'currency_id',
        'price',
        'setup_fee',
        'discount',
        'billing_period',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class
        );
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(
            CatalogProduct::class,
            'priceable_id'
        )->where(
            'priceable_type',
            'catalog_product'
        );
    }
}
