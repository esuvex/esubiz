<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceListing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'catalog_product_id',
        'workspace_id',

        'product_type',
        'product_id',
        'product_key',

        'uuid',
        'title',
        'slug',
        'summary',
        'description',

        'price',
        'currency',
        'commission_rate',
        'featured',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'featured' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function catalogProduct()
    {
        return $this->belongsTo(CatalogProduct::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isCoreAddon(): bool
    {
        return $this->product_type === 'core_addon';
    }

    public function isCoreBundle(): bool
    {
        return $this->product_type === 'core_bundle';
    }

    public function isCatalogProduct(): bool
    {
        return $this->catalog_product_id !== null;
    }
}
