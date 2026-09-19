<?php

namespace Esubiz\Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Category extends Model
{
    protected $table = 'ecommerce_categories';

    protected $fillable = [
        'website_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
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
                    'Ecommerce category requires an active website context.'
                );
            }

            $model->website_id = $websiteId;
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    protected static function currentWebsiteId(): ?int
    {
        $tenant = \App\Models\WebsiteTenant::current();
        $id = (int) ($tenant?->website_id ?? 0);

        return $id > 0 ? $id : null;
    }
}
