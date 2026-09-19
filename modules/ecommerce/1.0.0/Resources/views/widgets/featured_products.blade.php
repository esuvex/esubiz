{{-- ESUBIZ_ECOMMERCE_FEATURED_PRODUCTS_WIDGET_V2 --}}
@php
    $products = app(
        \Esubiz\Modules\Ecommerce\Services\StorefrontService::class
    )->featuredProducts($data ?? []);
@endphp

<div class="esubiz-ecommerce-widget esubiz-featured-products"
     data-ecommerce-widget="featured_products">
    @if(!empty($data['heading']))
        <h2>{{ $data['heading'] }}</h2>
    @endif

    <div class="esubiz-featured-products__items"
         data-columns="{{ (int) ($data['columns'] ?? 4) }}">
        @forelse($products as $product)
            <article class="esubiz-product-card">
                @if($product->image)
                    <img
                        src="{{ $product->image }}"
                        alt="{{ $product->name }}"
                        loading="lazy"
                    >
                @endif

                <h3>{{ $product->name }}</h3>

                <div class="esubiz-product-card__price">
                    {{ $product->currency }}
                    {{ number_format((float) $product->price, 2) }}
                </div>
            </article>
        @empty
            <div class="esubiz-ecommerce-empty">
                No featured products available.
            </div>
        @endforelse
    </div>
</div>
