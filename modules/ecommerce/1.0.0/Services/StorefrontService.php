<?php

namespace Esubiz\Modules\Ecommerce\Services;

use Esubiz\Modules\Ecommerce\Models\Category;
use Esubiz\Modules\Ecommerce\Models\Product;
use Illuminate\Support\Collection;

class StorefrontService
{
    public function products(array $options = []): Collection
    {
        $limit = $this->limit($options['limit'] ?? 8, 24);

        $query = Product::query()
            ->where('status', 'active')
            ->orderByDesc('id');

        $category = trim((string) ($options['category'] ?? ''));

        if ($category !== '') {
            $query->whereHas(
                'category',
                static fn ($q) => $q
                    ->where('slug', $category)
                    ->where('is_active', true)
            );
        }

        return $query->limit($limit)->get();
    }

    public function featuredProducts(array $options = []): Collection
    {
        $limit = $this->limit($options['limit'] ?? 4, 24);

        return Product::query()
            ->where('status', 'active')
            ->where('is_featured', true)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function categories(array $options = []): Collection
    {
        $limit = $this->limit($options['limit'] ?? 8, 24);

        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    protected function limit(mixed $value, int $maximum): int
    {
        return max(1, min((int) $value, $maximum));
    }
}
