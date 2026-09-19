<?php

namespace Esubiz\Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Product extends Model
{
    protected $table = 'ecommerce_products';

    protected $fillable = [
        'website_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'currency',
        'stock_quantity',
        'track_inventory',
        'product_type',
        'status',
        'is_featured',
        'image',
        'gallery',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_inventory' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('website', function (Builder $builder): void {
            $websiteId = static::currentWebsiteId();

            if ($websiteId !== null) {
                $builder->where(
                    $builder->getModel()->qualifyColumn('website_id'),
                    $websiteId
                );
            } else {
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function (self $model): void {
            $websiteId = static::currentWebsiteId();

            if ($websiteId === null) {
                throw new RuntimeException(
                    'Ecommerce product requires an active website context.'
                );
            }

            $model->website_id = $websiteId;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function currentWebsiteId(): ?int
    {
        $tenant = \App\Models\WebsiteTenant::current();
        $id = (int) ($tenant?->website_id ?? 0);

        return $id > 0 ? $id : null;
    }
}
