<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_category_id',
        'uuid',
        'name',
        'slug',
        'description',
        'product_type',
        'credit_quantity',
        'audience',
        'fulfilment_type',
        'is_featured',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'credit_quantity' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function module(): HasOne
    {
        return $this->hasOne(
            Module::class,
            'catalog_product_id'
        );
    }

    public function marketplaceCategories()
    {
        return $this->belongsToMany(
            MarketplaceCategory::class,
            'catalog_product_marketplace_category',
            'catalog_product_id',
            'marketplace_category_id'
        )->withTimestamps();
    }

}
