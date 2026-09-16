<?php

namespace App\Services\Core;

use App\Services\Media\EsubizImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/*
|--------------------------------------------------------------------------
| ESUBIZ_CORE_CANONICAL_WEBSITE_LOGO_V1
|--------------------------------------------------------------------------
|
| One Core website identity:
|
|   site_logo_path
|       Canonical/main website logo.
|
|   site_logo_white_path
|       Automatically generated white variant of the canonical logo.
|
| Theme/auth/email/etc. logo settings are optional overrides only.
| They must never become competing sources of website identity.
|
| Main logo:
|   SaaS Wizard / Site Settings
|       -> site_logo_path
|
| White logo:
|   generated from main logo
|       -> site_logo_white_path
|
| Consumers:
|   public branding -> explicit override -> site_logo_path
|   dark/footer/internal branding -> explicit override
|       -> site_logo_white_path
|       -> site_logo_path as final compatibility fallback
|
*/
class CoreWebsiteLogoService
{
    public function storeMainLogo(
        UploadedFile $file,
        int $websiteId
    ): array {
        $directory =
            'tenant-websites/'
            . $websiteId
            . '/media/branding';

        $mainPath = app(EsubizImageOptimizer::class)
            ->storeUploaded(
                $file,
                $directory,
                EsubizImageOptimizer::PROFILE_LOGO,
                'local'
            );

        $whitePath =
            $this->generateWhiteVariant(
                $mainPath,
                'local'
            );

        return [
            'main' => $mainPath,
            'white' => $whitePath,
        ];
    }

    /*
     * ESUBIZ_CORE_CANONICAL_LOGO_PUBLIC_URL_V4
     *
     * Canonical Core branding files live in tenant media storage.
     * They are delivered through the tenant /media route rather
     * than /storage or a filesystem-specific public URL.
     */
    public function publicUrl(
        ?string $path
    ): ?string {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, 'data:')
        ) {
            return $path;
        }

        return request()->getSchemeAndHttpHost()
            . '/media/'
            . implode(
                '/',
                array_map(
                    'rawurlencode',
                    explode(
                        '/',
                        ltrim($path, '/')
                    )
                )
            );
    }


    public function generateWhiteVariant(
        string $mainPath,
        string $disk = 'local'
    ): ?string {
        $mainPath = ltrim(trim($mainPath), '/');

        if ($mainPath === '') {
            return null;
        }

        $storage = Storage::disk($disk);

        if (!$storage->exists($mainPath)) {
            return null;
        }

        $bytes = $storage->get($mainPath);

        if ($bytes === '') {
            return null;
        }

        /*
         * SVG stays vector. Generate a white SVG variant by making
         * explicit non-none fill/stroke colours inherit currentColor,
         * then setting white as the root colour.
         *
         * This preserves transparency and avoids rasterising logos.
         */
        if (
            strtolower(
                pathinfo(
                    $mainPath,
                    PATHINFO_EXTENSION
                )
            ) === 'svg'
            || str_contains(
                strtolower(
                    substr(
                        ltrim($bytes),
                        0,
                        300
                    )
                ),
                '<svg'
            )
        ) {
            $svg = $bytes;

            $svg = preg_replace(
                '/<svg\b([^>]*)>/i',
                '<svg$1 style="color:#ffffff;">',
                $svg,
                1
            );

            $svg = preg_replace(
                '/\bfill\s*=\s*([\'"])(?!none\b|transparent\b)[^\'"]+\1/i',
                'fill="currentColor"',
                $svg
            );

            $svg = preg_replace(
                '/\bstroke\s*=\s*([\'"])(?!none\b|transparent\b)[^\'"]+\1/i',
                'stroke="currentColor"',
                $svg
            );

            $svg = preg_replace(
                '/fill\s*:\s*(?!none\b|transparent\b)[^;}"\']+/i',
                'fill:currentColor',
                $svg
            );

            $svg = preg_replace(
                '/stroke\s*:\s*(?!none\b|transparent\b)[^;}"\']+/i',
                'stroke:currentColor',
                $svg
            );

            $whitePath =
                preg_replace(
                    '/\.svg$/i',
                    '-white.svg',
                    $mainPath
                );

            if (!$whitePath || $whitePath === $mainPath) {
                $whitePath =
                    dirname($mainPath)
                    . '/logo-white.svg';
            }

            $storage->put(
                $whitePath,
                $svg
            );

            return $whitePath;
        }

        /*
         * Raster logo:
         * preserve every pixel's alpha channel while replacing visible
         * RGB values with pure white.
         */
        $source = @imagecreatefromstring($bytes);

        if (!$source) {
            return null;
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);

            if ($width < 1 || $height < 1) {
                return null;
            }

            $white = imagecreatetruecolor(
                $width,
                $height
            );

            if (!$white) {
                throw new RuntimeException(
                    'Unable to create white logo canvas.'
                );
            }

            try {
                imagealphablending(
                    $white,
                    false
                );

                imagesavealpha(
                    $white,
                    true
                );

                $transparent =
                    imagecolorallocatealpha(
                        $white,
                        255,
                        255,
                        255,
                        127
                    );

                imagefill(
                    $white,
                    0,
                    0,
                    $transparent
                );

                for ($y = 0; $y < $height; $y++) {
                    for ($x = 0; $x < $width; $x++) {
                        $rgba =
                            imagecolorat(
                                $source,
                                $x,
                                $y
                            );

                        $alpha =
                            ($rgba >> 24)
                            & 0x7F;

                        $pixel =
                            imagecolorallocatealpha(
                                $white,
                                255,
                                255,
                                255,
                                $alpha
                            );

                        imagesetpixel(
                            $white,
                            $x,
                            $y,
                            $pixel
                        );
                    }
                }

                ob_start();

                if (!imagepng($white, null, 7)) {
                    ob_end_clean();

                    throw new RuntimeException(
                        'Unable to encode white logo.'
                    );
                }

                $whiteBytes =
                    (string) ob_get_clean();

                $whitePath =
                    preg_replace(
                        '/\.[^.\/]+$/',
                        '-white.png',
                        $mainPath
                    );

                if (
                    !$whitePath
                    || $whitePath === $mainPath
                ) {
                    $whitePath =
                        dirname($mainPath)
                        . '/logo-white.png';
                }

                $storage->put(
                    $whitePath,
                    $whiteBytes
                );

                return $whitePath;
            } finally {
                imagedestroy($white);
            }
        } finally {
            imagedestroy($source);
        }
    }
}
