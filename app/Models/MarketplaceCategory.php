<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'product_types',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'product_types' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Determine whether this category may be used by a product type.
     *
     * An empty product_types value means universal compatibility.
     */
    public function supportsProductType(string $productType): bool
    {
        $types = array_values(
            array_filter(
                array_map(
                    fn ($type) => strtolower(trim((string) $type)),
                    $this->product_types ?? []
                )
            )
        );

        if ($types === []) {
            return true;
        }

        return in_array(
            strtolower(trim($productType)),
            $types,
            true
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function catalogProducts()
    {
        return $this->belongsToMany(
            CatalogProduct::class,
            'catalog_product_marketplace_category',
            'marketplace_category_id',
            'catalog_product_id'
        )->withTimestamps();
    }

}
