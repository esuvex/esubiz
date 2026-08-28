@props([
    'src' => null,
    'controls' => true,
    'autoplay' => false,
    'loop' => false,
    'preload' => 'none',
])

{{--
    ESUBIZ_GLOBAL_MEDIA_AUDIO_COMPONENT_V1

    Audio is not downloaded with the page by default.
--}}
<audio
    preload="{{
        in_array(
            $preload,
            ['none', 'metadata', 'auto'],
            true
        )
            ? $preload
            : 'none'
    }}"

    @if($controls)
        controls
    @endif

    @if($autoplay)
        autoplay
    @endif

    @if($loop)
        loop
    @endif

    @if($src)
        src="{{ $src }}"
    @endif

    {{ $attributes }}
>
    {{ $slot }}
</audio>
