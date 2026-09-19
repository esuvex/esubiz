{{-- ESUBIZ_ECOMMERCE_CART_WIDGET_V1 --}}
<div class="esubiz-ecommerce-widget esubiz-cart"
     data-ecommerce-widget="cart">
    @if(!empty($data['heading']))
        <h2>{{ $data['heading'] }}</h2>
    @endif

    <div data-ecommerce-cart></div>
</div>
