{{-- ESUBIZ_ECOMMERCE_PRODUCT_CATEGORIES_WIDGET_V2 --}}
@php
    $categories = app(
        \Esubiz\Modules\Ecommerce\Services\StorefrontService::class
    )->categories($data ?? []);
@endphp

<div class="esubiz-ecommerce-widget esubiz-product-categories"
     data-ecommerce-widget="product_categories">
    @if(!empty($data['heading']))
        <h2>{{ $data['heading'] }}</h2>
    @endif

    <div class="esubiz-product-categories__items"
         data-columns="{{ (int) ($data['columns'] ?? 4) }}">
        @forelse($categories as $category)
            <article class="esubiz-category-card">
                @if($category->image)
                    <img
                        src="{{ $category->image }}"
                        alt="{{ $category->name }}"
                        loading="lazy"
                    >
                @endif

                <h3>{{ $category->name }}</h3>
            </article>
        @empty
            <div class="esubiz-ecommerce-empty">
                No product categories available.
            </div>
        @endforelse
    </div>
</div>
