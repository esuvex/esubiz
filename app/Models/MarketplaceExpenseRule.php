<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketplaceExpenseRule extends Model
{
    protected $fillable = [
        'product_type',
        'name',
        'calculation_type',
        'value',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeForProduct(
        Builder $query,
        string $productType
    ): Builder {
        return $query->where('product_type', $productType);
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(
        Builder $query
    ): Builder {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
