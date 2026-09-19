{{-- ESUBIZ_ECOMMERCE_CHECKOUT_WIDGET_V1 --}}
<div class="esubiz-ecommerce-widget esubiz-checkout"
     data-ecommerce-widget="checkout">
    @if(!empty($data['heading']))
        <h2>{{ $data['heading'] }}</h2>
    @endif

    <div data-ecommerce-checkout></div>
</div>
