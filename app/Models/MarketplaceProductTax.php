<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductTax extends Model
{
    protected $fillable = [
        'product_type',
        'central_tax_id',
        'is_exclusive',
        'is_active',
    ];

    protected $casts = [
        'central_tax_id' => 'integer',
        'is_exclusive' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function centralTax(): BelongsTo
    {
        return $this->belongsTo(CentralTax::class);
    }

    public function scopeForProduct(
        Builder $query,
        string $productType
    ): Builder {
        return $query->where('product_type', $productType);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
