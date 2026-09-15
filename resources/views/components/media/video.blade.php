@props([
    /*
     * ESUBIZ_CENTRAL_HTML5_VIDEO_PLAYER_V2
     *
     * Canonical native HTML5 video player for Central Esubiz.
     *
     * Upload/transcoding remains the responsibility of
     * CentralMediaService / FFmpeg.
     *
     * This component handles browser playback only.
     *
     * Examples:
     *
     * <x-media.video
     *     :src="$videoUrl"
     *     :poster="$posterUrl"
     * />
     *
     * <x-media.video
     *     :sources="[
     *         ['src' => $mp4Url, 'type' => 'video/mp4'],
     *         ['src' => $webmUrl, 'type' => 'video/webm'],
     *     ]"
     * />
     */

    'src' => null,

    /*
     * Optional MIME type for the single src.
     *
     * Central optimized uploads normally resolve to video/mp4.
     */
    'type' => null,

    /*
     * Optional multiple-source array:
     *
     * [
     *     ['src' => '...', 'type' => 'video/mp4'],
     *     ['src' => '...', 'type' => 'video/webm'],
     * ]
     */
    'sources' => [],

    'poster' => null,

    'controls' => true,
    'autoplay' => false,
    'muted' => false,
    'loop' => false,
    'playsinline' => true,

    /*
     * metadata is the Central default:
     * enough information for duration/dimensions without
     * eagerly downloading the complete video.
     */
    'preload' => 'metadata',

    /*
     * Optional accessibility label.
     */
    'label' => 'Video player',

    /*
     * Optional intrinsic dimensions.
     */
    'width' => null,
    'height' => null,

    /*
     * Native browser controls policy.
     *
     * Leave null for the browser default.
     */
    'controlsList' => null,

    /*
     * Keep native Picture-in-Picture enabled by default.
     */
    'disablePictureInPicture' => false,

    /*
     * Keep remote playback available by default.
     */
    'disableRemotePlayback' => false,

    /*
     * Optional CORS mode:
     * anonymous | use-credentials
     */
    'crossorigin' => null,
])

@php
    /*
     * ESUBIZ_CENTRAL_HTML5_VIDEO_PLAYER_NORMALIZATION_V2
     */

    $esVideoPreloadV2 =
        in_array(
            $preload,
            [
                'none',
                'metadata',
                'auto',
            ],
            true
        )
            ? $preload
            : 'metadata';

    $esVideoCrossoriginV2 =
        in_array(
            $crossorigin,
            [
                'anonymous',
                'use-credentials',
            ],
            true
        )
            ? $crossorigin
            : null;

    /*
     * Browsers generally require muted playback for autoplay.
     *
     * When autoplay is requested we therefore make the rendered
     * player muted automatically rather than creating a player
     * that silently fails to autoplay on most browsers.
     */
    $esVideoMutedV2 =
        (bool) $muted
        || (bool) $autoplay;

    $esVideoSourcesV2 = [];

    if (
        is_array($sources)
        || $sources instanceof \Traversable
    ) {
        foreach ($sources as $source) {
            if (is_string($source)) {
                $source = [
                    'src' => $source,
                ];
            }

            if (!is_array($source)) {
                continue;
            }

            $sourceUrl =
                trim(
                    (string) (
                        $source['src']
                        ?? ''
                    )
                );

            if ($sourceUrl === '') {
                continue;
            }

            $sourceMime =
                trim(
                    (string) (
                        $source['type']
                        ?? ''
                    )
                );

            $esVideoSourcesV2[] = [
                'src' => $sourceUrl,
                'type' =>
                    $sourceMime !== ''
                        ? $sourceMime
                        : null,
            ];
        }
    }

    /*
     * Single-source compatibility.
     *
     * Existing/current callers can continue passing only :src.
     */
    $esSingleVideoSrcV2 =
        trim(
            (string) (
                $src
                ?? ''
            )
        );

    $esSingleVideoTypeV2 =
        trim(
            (string) (
                $type
                ?? ''
            )
        );

    /*
     * Infer common browser MIME types when the caller supplied
     * a simple src without an explicit type.
     */
    if (
        $esSingleVideoSrcV2 !== ''
        && $esSingleVideoTypeV2 === ''
    ) {
        $esVideoPathV2 =
            strtolower(
                (string) parse_url(
                    $esSingleVideoSrcV2,
                    PHP_URL_PATH
                )
            );

        if (
            str_ends_with(
                $esVideoPathV2,
                '.mp4'
            )
            || str_ends_with(
                $esVideoPathV2,
                '.m4v'
            )
        ) {
            $esSingleVideoTypeV2 =
                'video/mp4';
        } elseif (
            str_ends_with(
                $esVideoPathV2,
                '.webm'
            )
        ) {
            $esSingleVideoTypeV2 =
                'video/webm';
        } elseif (
            str_ends_with(
                $esVideoPathV2,
                '.ogg'
            )
            || str_ends_with(
                $esVideoPathV2,
                '.ogv'
            )
        ) {
            $esSingleVideoTypeV2 =
                'video/ogg';
        }
    }

    /*
     * Sanitize dimensions.
     */
    $esVideoWidthV2 =
        is_numeric($width)
        && (int) $width > 0
            ? (int) $width
            : null;

    $esVideoHeightV2 =
        is_numeric($height)
        && (int) $height > 0
            ? (int) $height
            : null;
@endphp

@if(
    $esSingleVideoSrcV2 !== ''
    || count($esVideoSourcesV2) > 0
)
    <video
        preload="{{ $esVideoPreloadV2 }}"

        aria-label="{{ $label }}"

        @if($controls)
            controls
        @endif

        @if($autoplay)
            autoplay
        @endif

        @if($esVideoMutedV2)
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

        @if($esVideoWidthV2)
            width="{{ $esVideoWidthV2 }}"
        @endif

        @if($esVideoHeightV2)
            height="{{ $esVideoHeightV2 }}"
        @endif

        @if($controlsList)
            controlslist="{{ $controlsList }}"
        @endif

        @if($disablePictureInPicture)
            disablepictureinpicture
        @endif

        @if($disableRemotePlayback)
            disableremoteplayback
        @endif

        @if($esVideoCrossoriginV2)
            crossorigin="{{ $esVideoCrossoriginV2 }}"
        @endif

        {{ $attributes->merge([
            'style' =>
                'display:block;width:100%;max-width:100%;height:auto;',
        ]) }}
    >
        @foreach(
            $esVideoSourcesV2
            as $esVideoSourceV2
        )
            <source
                src="{{ $esVideoSourceV2['src'] }}"

                @if($esVideoSourceV2['type'])
                    type="{{ $esVideoSourceV2['type'] }}"
                @endif
            >
        @endforeach

        @if($esSingleVideoSrcV2 !== '')
            <source
                src="{{ $esSingleVideoSrcV2 }}"

                @if($esSingleVideoTypeV2 !== '')
                    type="{{ $esSingleVideoTypeV2 }}"
                @endif
            >
        @endif

        {{ $slot }}

        Your browser does not support HTML5 video.
    </video>
@endif
