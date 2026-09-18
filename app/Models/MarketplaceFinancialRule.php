<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketplaceFinancialRule extends Model
{
    protected $fillable = [
        'product_type',
        'developer_share_percent',
        'expense_percent',
        'is_active',
    ];

    protected $casts = [
        'developer_share_percent' => 'decimal:4',
        'expense_percent' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForProductType(
        Builder $query,
        string $productType
    ): Builder {
        return $query->where(
            'product_type',
            $productType
        );
    }
}
