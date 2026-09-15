<?php

namespace App\Services\Platform;

use App\Services\Core\CorePhoneCountryCatalog;

class CentralCountryCatalog
{
    /**
     * ESUBIZ_CENTRAL_WORLDWIDE_COUNTRY_CATALOG_V26
     *
     * Central adapter over the shared Core worldwide phone
     * country catalogue.
     *
     * CorePhoneCountryCatalog remains the source of truth for:
     * - worldwide countries;
     * - ISO alpha-2 country codes;
     * - international phone dial codes;
     * - Nigeria-first ordering.
     *
     * This adapter preserves Central's established format:
     *
     * ISO alpha-2 => [country name, phone dial code]
     *
     * so existing Central registration/profile consumers do
     * not need to understand Core's internal array structure.
     */
    public function __construct(
        protected CorePhoneCountryCatalog $coreCountries
    ) {
    }

    public function all(): array
    {
        $result = [];

        foreach ($this->coreCountries->all() as $country) {
            $countryCode = strtoupper(
                trim((string) ($country['country_code'] ?? ''))
            );

            $countryName = trim(
                (string) ($country['country'] ?? '')
            );

            $dialCode = trim(
                (string) ($country['dial_code'] ?? '')
            );

            if (
                $countryCode === ''
                || $countryName === ''
                || $dialCode === ''
            ) {
                continue;
            }

            $result[$countryCode] = [
                $countryName,
                $dialCode,
            ];
        }

        return $result;
    }

    public function dialCode(
        ?string $countryCode,
        string $fallback = '+234'
    ): string {
        $countryCode = strtoupper(
            trim((string) $countryCode)
        );

        $country =
            $this->coreCountries->findByCountryCode(
                $countryCode
            );

        return $country['dial_code']
            ?? $fallback;
    }

    public function has(?string $countryCode): bool
    {
        $countryCode = strtoupper(
            trim((string) $countryCode)
        );

        if ($countryCode === '') {
            return false;
        }

        return $this->coreCountries
            ->findByCountryCode($countryCode) !== null;
    }
}
