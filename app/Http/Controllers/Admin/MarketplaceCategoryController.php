<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarketplaceCategoryController extends Controller
{
    private const PRODUCT_TYPES = [
        'theme',
        'module',
        'addon',
        'bundle',
        'website_type',
    ];

    public function index(Request $request)
    {
        $categories = MarketplaceCategory::query()
            ->withCount('catalogProducts')
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.marketplace.settings.index',
            [
                'categories' => $categories,
                'productTypes' => self::PRODUCT_TYPES,
            ]
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            MarketplaceCategory::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'product_types' => $data['product_types'] ?? [],
                'is_active' => (bool) ($data['is_active'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);
        });

        return back()->with(
            'success',
            'Marketplace category created.'
        );
    }

    public function update(
        Request $request,
        MarketplaceCategory $marketplaceCategory
    ) {
        $data = $this->validatedData($request);

        DB::transaction(
            function () use ($data, $marketplaceCategory) {
                $marketplaceCategory->update([
                    'name' => $data['name'],
                    'slug' => $this->uniqueSlug(
                        $data['name'],
                        $marketplaceCategory->id
                    ),
                    'description' => $data['description'] ?? null,
                    'product_types' => $data['product_types'] ?? [],
                    'is_active' => (bool) ($data['is_active'] ?? false),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                ]);
            }
        );

        return back()->with(
            'success',
            'Marketplace category updated.'
        );
    }

    public function destroy(
        MarketplaceCategory $marketplaceCategory
    ) {
        DB::transaction(
            function () use ($marketplaceCategory) {
                $marketplaceCategory
                    ->catalogProducts()
                    ->detach();

                $marketplaceCategory->delete();
            }
        );

        return back()->with(
            'success',
            'Marketplace category deleted.'
        );
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'product_types' => [
                'nullable',
                'array',
            ],

            'product_types.*' => [
                'string',
                Rule::in(self::PRODUCT_TYPES),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $number = 2;

        while (
            MarketplaceCategory::withTrashed()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) =>
                        $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $base . '-' . $number++;
        }

        return $slug;
    }
}
