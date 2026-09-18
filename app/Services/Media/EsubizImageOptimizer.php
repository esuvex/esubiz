<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * ESUBIZ_GLOBAL_IMAGE_OPTIMIZER_V1
 *
 * Canonical image optimization pipeline for Central + Core.
 *
 * Rules:
 * - Preserve PNG / WEBP transparency.
 * - Never flatten alpha backgrounds.
 * - Correct EXIF orientation for JPEG where available.
 * - Never upscale images unnecessarily.
 * - Support profile, favicon, logo and general-photo profiles.
 * - SVG is intentionally not rasterized here.
 *
 * Future Central/Core image upload features should route raster images
 * through this service instead of duplicating image-processing logic.
 */
class EsubizImageOptimizer
{
    public const PROFILE_GENERAL = 'general';
    public const PROFILE_PHOTO = 'profile';
    public const PROFILE_LOGO = 'logo';
    public const PROFILE_FAVICON = 'favicon';
    public const PROFILE_MARKETPLACE_PREVIEW = 'marketplace_preview';

    /**
     * Optimize an uploaded raster image and store it.
     *
     * Returns the path relative to the selected Laravel disk.
     */
    public function storeUploaded(
        UploadedFile $file,
        string $directory,
        string $profile = self::PROFILE_GENERAL,
        string $disk = 'public',
        ?string $filename = null
    ): string {
        if (!$file->isValid()) {
            throw new RuntimeException('The uploaded image is invalid.');
        }

        $mime = strtolower((string) $file->getMimeType());

        if ($mime === 'image/svg+xml') {
            throw new RuntimeException(
                'SVG files must use the SVG-safe storage pipeline and must not be rasterized.'
            );
        }

        if (!in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            throw new RuntimeException(
                'Unsupported image type. Expected JPEG, PNG or WEBP.'
            );
        }

        if (
            !function_exists('imagecreatetruecolor')
            || !function_exists('imagecopyresampled')
        ) {
            throw new RuntimeException(
                'GD image processing is unavailable on this server.'
            );
        }

        $source = $this->createSourceImage(
            $file->getRealPath(),
            $mime
        );

        if (!$source) {
            throw new RuntimeException(
                'The uploaded image could not be decoded.'
            );
        }

        try {
            if ($mime === 'image/jpeg') {
                $source = $this->orientJpeg(
                    $source,
                    $file->getRealPath()
                );
            }

            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            if ($sourceWidth < 1 || $sourceHeight < 1) {
                throw new RuntimeException(
                    'The uploaded image dimensions are invalid.'
                );
            }

            [$targetWidth, $targetHeight, $crop] =
                $this->targetGeometry(
                    $sourceWidth,
                    $sourceHeight,
                    $profile
                );

            $target = imagecreatetruecolor(
                $targetWidth,
                $targetHeight
            );

            if (!$target) {
                throw new RuntimeException(
                    'Unable to allocate optimized image canvas.'
                );
            }

            try {
                /*
                 * Alpha is always prepared even for JPEG input.
                 *
                 * This is critical for PNG/WEBP logos, favicons and
                 * graphics because their transparent background must
                 * survive the optimization process.
                 */
                imagealphablending($target, false);
                imagesavealpha($target, true);

                $transparent = imagecolorallocatealpha(
                    $target,
                    0,
                    0,
                    0,
                    127
                );

                imagefilledrectangle(
                    $target,
                    0,
                    0,
                    $targetWidth,
                    $targetHeight,
                    $transparent
                );

                if ($crop) {
                    /*
                     * Centre-crop to the TARGET aspect ratio.
                     *
                     * This preserves the existing square Profile Photo
                     * behaviour while allowing Marketplace previews to
                     * use the canonical 8:5 canvas without distortion.
                     */
                    $sourceRatio =
                        $sourceWidth / $sourceHeight;

                    $targetRatio =
                        $targetWidth / $targetHeight;

                    if ($sourceRatio > $targetRatio) {
                        $cropHeight = $sourceHeight;

                        $cropWidth = (int) round(
                            $sourceHeight * $targetRatio
                        );
                    } else {
                        $cropWidth = $sourceWidth;

                        $cropHeight = (int) round(
                            $sourceWidth / $targetRatio
                        );
                    }

                    $sourceX = (int) floor(
                        ($sourceWidth - $cropWidth) / 2
                    );

                    $sourceY = (int) floor(
                        ($sourceHeight - $cropHeight) / 2
                    );

                    imagecopyresampled(
                        $target,
                        $source,
                        0,
                        0,
                        $sourceX,
                        $sourceY,
                        $targetWidth,
                        $targetHeight,
                        $cropWidth,
                        $cropHeight
                    );
                } else {
                    imagecopyresampled(
                        $target,
                        $source,
                        0,
                        0,
                        0,
                        0,
                        $targetWidth,
                        $targetHeight,
                        $sourceWidth,
                        $sourceHeight
                    );
                }

                $outputMime = $this->outputMime(
                    $mime,
                    $profile
                );

                $extension = match ($outputMime) {
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    default => 'webp',
                };

                if ($filename === null || trim($filename) === '') {
                    $filename =
                        'image-'
                        . time()
                        . '-'
                        . bin2hex(random_bytes(5))
                        . '.'
                        . $extension;
                } else {
                    $filename = pathinfo(
                        $filename,
                        PATHINFO_FILENAME
                    ) . '.' . $extension;
                }

                $directory = trim($directory, '/');

                $relativePath =
                    ($directory !== ''
                        ? $directory . '/'
                        : '')
                    . $filename;

                $temporaryPath = tempnam(
                    sys_get_temp_dir(),
                    'esubiz-img-'
                );

                if (!$temporaryPath) {
                    throw new RuntimeException(
                        'Unable to allocate temporary image storage.'
                    );
                }

                try {
                    $saved = $this->encode(
                        $target,
                        $temporaryPath,
                        $outputMime
                    );

                    if (!$saved) {
                        throw new RuntimeException(
                            'The optimized image could not be encoded.'
                        );
                    }

                    $stream = fopen(
                        $temporaryPath,
                        'rb'
                    );

                    if ($stream === false) {
                        throw new RuntimeException(
                            'The optimized image could not be read.'
                        );
                    }

                    try {
                        $written = Storage::disk($disk)->put(
                            $relativePath,
                            $stream
                        );
                    } finally {
                        fclose($stream);
                    }

                    if (!$written) {
                        throw new RuntimeException(
                            'The optimized image could not be stored.'
                        );
                    }

                    return $relativePath;
                } finally {
                    @unlink($temporaryPath);
                }
            } finally {
                imagedestroy($target);
            }
        } finally {
            imagedestroy($source);
        }
    }

    /**
     * Decide dimensions and whether centre-cropping is allowed.
     */
    private function targetGeometry(
        int $width,
        int $height,
        string $profile
    ): array {
        if ($profile === self::PROFILE_PHOTO) {
            return [512, 512, true];
        }

        /*
         * Canonical Esubiz Marketplace preview canvas.
         *
         * Used for Themes, Modules, Add-ons, Bundles and
         * Website Types irrespective of publisher.
         */
        if ($profile === self::PROFILE_MARKETPLACE_PREVIEW) {
            return [1600, 1000, true];
        }

        $maxWidth = match ($profile) {
            self::PROFILE_FAVICON => 512,
            self::PROFILE_LOGO => 1600,
            default => 1920,
        };

        $maxHeight = match ($profile) {
            self::PROFILE_FAVICON => 512,
            self::PROFILE_LOGO => 1200,
            default => 1920,
        };

        /*
         * Never enlarge an already-small image.
         */
        $scale = min(
            1,
            $maxWidth / $width,
            $maxHeight / $height
        );

        $targetWidth = max(
            1,
            (int) round($width * $scale)
        );

        $targetHeight = max(
            1,
            (int) round($height * $scale)
        );

        /*
         * Favicon and logo aspect ratios are preserved.
         * They are NOT forcibly cropped.
         *
         * A transparent 512x512 favicon remains transparent,
         * while a rectangular source remains proportional.
         */
        return [
            $targetWidth,
            $targetHeight,
            false,
        ];
    }

    /**
     * Preserve alpha-capable source formats.
     *
     * PNG remains PNG because it may contain transparent pixels.
     * WEBP remains WEBP and preserves alpha.
     * JPEG general photography may be emitted as WEBP for efficiency.
     */
    private function outputMime(
        string $sourceMime,
        string $profile
    ): string {
        if ($sourceMime === 'image/png') {
            return 'image/png';
        }

        if ($sourceMime === 'image/webp') {
            return 'image/webp';
        }

        /*
         * Logos/favicons originating as JPEG have no alpha to preserve,
         * but WEBP gives efficient output.
         */
        return 'image/webp';
    }

    private function createSourceImage(
        string $path,
        string $mime
    ) {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),

            'image/png' => @imagecreatefrompng($path),

            'image/webp' =>
                function_exists('imagecreatefromwebp')
                    ? @imagecreatefromwebp($path)
                    : false,

            default => false,
        };
    }

    /**
     * Correct phone/camera JPEG orientation when EXIF is available.
     */
    private function orientJpeg(
        $image,
        string $path
    ) {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);

        $orientation = (int) (
            $exif['Orientation']
            ?? 1
        );

        $rotated = match ($orientation) {
            3 => imagerotate(
                $image,
                180,
                0
            ),

            6 => imagerotate(
                $image,
                -90,
                0
            ),

            8 => imagerotate(
                $image,
                90,
                0
            ),

            default => false,
        };

        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function encode(
        $image,
        string $path,
        string $mime
    ): bool {
        return match ($mime) {
            'image/png' =>
                imagepng(
                    $image,
                    $path,
                    7
                ),

            'image/jpeg' =>
                imagejpeg(
                    $image,
                    $path,
                    86
                ),

            'image/webp' =>
                function_exists('imagewebp')
                    ? imagewebp(
                        $image,
                        $path,
                        84
                    )
                    : false,

            default => false,
        };
    }
}
