@props([
    'src' => null,
    'alt' => '',
    'priority' => false,
    'width' => null,
    'height' => null,
])

@if($src)
    {{--
        ESUBIZ_GLOBAL_MEDIA_IMAGE_COMPONENT_V1

        Default:
        - lazy loading
        - asynchronous decoding

        Priority/LCP images:
        - eager loading
        - high fetch priority

        Storage ownership is unaffected. This component only
        controls when the browser requests the referenced asset.
    --}}
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $priority ? 'eager' : 'lazy' }}"
        decoding="async"

        @if($priority)
            fetchpriority="high"
        @endif

        @if($width)
            width="{{ $width }}"
        @endif

        @if($height)
            height="{{ $height }}"
        @endif

        {{ $attributes }}
    >
@endif
