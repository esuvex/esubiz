{{-- ESUBIZ_ECOMMERCE_PRODUCT_GRID_WIDGET_V2 --}}
@php
    $products = app(
        \Esubiz\Modules\Ecommerce\Services\StorefrontService::class
    )->products($data ?? []);
@endphp

<div class="esubiz-ecommerce-widget esubiz-product-grid"
     data-ecommerce-widget="product_grid">
    @if(!empty($data['heading']))
        <h2>{{ $data['heading'] }}</h2>
    @endif

    <div class="esubiz-product-grid__items"
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

                @if(($data['showPrice'] ?? true))
                    <div class="esubiz-product-card__price">
                        {{ $product->currency }}
                        {{ number_format((float) $product->price, 2) }}
                    </div>
                @endif
            </article>
        @empty
            <div class="esubiz-ecommerce-empty">
                No products available.
            </div>
        @endforelse
    </div>
</div>
