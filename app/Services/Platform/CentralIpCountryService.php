<?php

namespace App\Services\Platform;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CentralIpCountryService
{
    /*
     * ESUBIZ_CENTRAL_IP_COUNTRY_V2
     *
     * Country-only IP detection for Central Esubiz.
     *
     * Provider:
     * IPinfo Lite.
     *
     * Design:
     * - server-side lookup only
     * - free authenticated Lite API
     * - country data only
     * - IP is never persisted in Esubiz database
     * - cache key stores only a SHA-256 hash of the IP
     * - successful result cached for 24 hours
     * - failures cached briefly to avoid repeated API calls
     * - private/reserved addresses are never submitted
     * - provider failure safely returns null
     */
    public function countryCode(
        Request $request
    ): ?string {

        /*
         * ESUBIZ_CLOUDFLARE_COUNTRY_RESOLUTION_V4
         *
         * Preferred source for proxied Esubiz traffic.
         *
         * Cloudflare resolves the visitor country before PHP,
         * avoiding proxy-IP mistakes and external API latency.
         */
        $cloudflareCountry = strtoupper(
            trim(
                (string) $request->header(
                    'CF-IPCountry',
                    ''
                )
            )
        );

        if (
            preg_match(
                '/^[A-Z]{2}$/',
                $cloudflareCountry
            )
            && !in_array(
                $cloudflareCountry,
                [
                    'XX',
                    'T1',
                ],
                true
            )
        ) {
            return $cloudflareCountry;
        }


        $ip = trim(
            (string) $request->ip()
        );

        if (!$this->isPublicIp($ip)) {
            return null;
        }

        $token = trim(
            (string) config(
                'services.ipinfo_lite.token',
                ''
            )
        );

        if ($token === '') {
            return null;
        }

        $cacheKey =
            'esubiz:central:ip-country:'
            . hash('sha256', $ip);

        $failureKey =
            $cacheKey . ':failed';

        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            $cached = $this->normalizeCountry(
                $cached
            );

            if ($cached !== null) {
                return $cached;
            }
        }

        if (Cache::has($failureKey)) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(3)
                ->connectTimeout(2)
                ->get(
                    'https://api.ipinfo.io/lite/'
                        . rawurlencode($ip),
                    [
                        'token' => $token,
                    ]
                );

            if (!$response->successful()) {
                Cache::put(
                    $failureKey,
                    true,
                    now()->addMinutes(10)
                );

                return null;
            }

            $country = $this->normalizeCountry(
                $response->json(
                    'country_code'
                )
            );

            if ($country === null) {
                Cache::put(
                    $failureKey,
                    true,
                    now()->addMinutes(10)
                );

                return null;
            }

            Cache::put(
                $cacheKey,
                $country,
                now()->addDay()
            );

            Cache::forget($failureKey);

            return $country;
        } catch (\Throwable $exception) {
            Cache::put(
                $failureKey,
                true,
                now()->addMinutes(10)
            );

            return null;
        }
    }

    protected function normalizeCountry(
        mixed $country
    ): ?string {
        $country = strtoupper(
            trim((string) $country)
        );

        if (
            !preg_match(
                '/^[A-Z]{2}$/',
                $country
            )
        ) {
            return null;
        }

        return $country;
    }

    protected function isPublicIp(
        string $ip
    ): bool {
        if ($ip === '') {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE
                | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
