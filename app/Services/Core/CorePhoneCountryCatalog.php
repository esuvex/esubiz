<?php

namespace App\Services\Core;

use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;

class CorePhoneCountryCatalog
{
    public function all(): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $countries = Countries::getNames('en');
        $result = [];

        foreach ($countries as $countryCode => $countryName) {
            try {
                $callingCode = $phoneUtil->getCountryCodeForRegion(
                    strtoupper($countryCode)
                );
            } catch (\Throwable) {
                continue;
            }

            if (!$callingCode) {
                continue;
            }

            $result[] = [
                'country_code' => strtoupper($countryCode),
                'country' => $countryName,
                'dial_code' => '+' . $callingCode,
            ];
        }

        usort(
            $result,
            function (array $a, array $b): int {
                if ($a['country_code'] === 'NG') {
                    return -1;
                }

                if ($b['country_code'] === 'NG') {
                    return 1;
                }

                return strcmp($a['country'], $b['country']);
            }
        );

        return $result;
    }

    public function default(): array
    {
        return [
            'country_code' => 'NG',
            'country' => 'Nigeria',
            'dial_code' => '+234',
        ];
    }

    public function findByCountryCode(string $countryCode): ?array
    {
        $countryCode = strtoupper(trim($countryCode));

        foreach ($this->all() as $country) {
            if ($country['country_code'] === $countryCode) {
                return $country;
            }
        }

        return null;
    }

    public function findByDialCode(string $dialCode): ?array
    {
        $dialCode = trim($dialCode);

        if ($dialCode !== '' && !str_starts_with($dialCode, '+')) {
            $dialCode = '+' . $dialCode;
        }

        foreach ($this->all() as $country) {
            if ($country['dial_code'] === $dialCode) {
                return $country;
            }
        }

        return null;
    }

    public function normalizeDialCode(string $dialCode): string
    {
        $digits = preg_replace('/[^0-9]/', '', $dialCode) ?? '';

        return $digits === '' ? '' : '+' . $digits;
    }
}
