<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketplaceReferralRule extends Model
{
    protected $fillable = [
        'product_type',
        'referrer_role',
        'level_one_commission_percent',
        'is_active',
    ];

    protected $casts = [
        'level_one_commission_percent' => 'decimal:4',
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

    public function scopeForRole(
        Builder $query,
        string $role
    ): Builder {
        return $query->where(
            'referrer_role',
            $role
        );
    }
}
