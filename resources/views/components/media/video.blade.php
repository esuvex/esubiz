@props([
    'src' => null,
    'poster' => null,
    'controls' => true,
    'autoplay' => false,
    'muted' => false,
    'loop' => false,
    'playsinline' => true,
    'preload' => 'none',
])

{{--
    ESUBIZ_GLOBAL_MEDIA_VIDEO_COMPONENT_V1

    Videos are not downloaded with the page by default.
    Actual media begins loading only when explicitly required
    by the browser/user interaction.

    FFmpeg optimization is handled separately at upload time.
--}}
<video
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

    @if($muted)
        muted
    @endif

    @if($loop)
        loop
    @endif

    @if($playsinline)
        playsinline
    @endif

    @if($poster)
        poster="{{ $poster }}"
    @endif

    @if($src)
        src="{{ $src }}"
    @endif

    {{ $attributes }}
>
    {{ $slot }}
</video>
