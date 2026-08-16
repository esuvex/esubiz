<?php

namespace App\Services\Core;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class CoreCurrencyCatalog
{
    /**
     * Return all currently active/legal-tender currencies with
     * their associated countries/regions.
     *
     * Currency data comes from Symfony Intl / ICU rather than
     * a manually maintained partial list.
     */
    public function all(): array
    {
        $countries = Countries::getNames('en');
        $result = [];

        foreach ($countries as $countryCode => $countryName) {
            try {
                $currencyCodes = Currencies::forCountry(
                    $countryCode,
                    legalTender: true,
                    active: true
                );
            } catch (\Throwable) {
                continue;
            }

            foreach ($currencyCodes as $currencyCode) {
                if (!Currencies::exists($currencyCode)) {
                    continue;
                }

                $result[] = [
                    'country_code' => $countryCode,
                    'country' => $countryName,
                    'currency' => Currencies::getName(
                        $currencyCode,
                        'en'
                    ),
                    'code' => $currencyCode,
                    'symbol' => $this->symbol($currencyCode),
                ];
            }
        }

        usort(
            $result,
            fn ($a, $b) => strcmp(
                $a['country'],
                $b['country']
            )
        );

        return $result;
    }

    /**
     * Search currencies/countries.
     *
     * Designed for live search: call this method on each
     * search request as the user types.
     */
    public function search(
        string $query,
        int $limit = 25
    ): array {
        $query = trim($query);

        if ($query === '') {
            return array_slice($this->all(), 0, $limit);
        }

        $query = mb_strtolower($query);

        $matches = array_filter(
            $this->all(),
            function (array $currency) use ($query): bool {
                return str_contains(
                    mb_strtolower($currency['country']),
                    $query
                )
                || str_contains(
                    mb_strtolower($currency['currency']),
                    $query
                )
                || str_contains(
                    mb_strtolower($currency['code']),
                    $query
                )
                || str_contains(
                    mb_strtolower($currency['symbol']),
                    $query
                );
            }
        );

        return array_slice(
            array_values($matches),
            0,
            max(1, $limit)
        );
    }

    protected function symbol(string $currencyCode): string
    {
        return match ($currencyCode) {
            'NGN' => '₦',
            'GHS' => '₵',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'KRW' => '₩',
            'ZAR' => 'R',
            'KES' => 'KSh',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'CHF' => 'CHF',
            'CAD' => '$',
            'AUD' => '$',
            default => Currencies::getSymbol(
                $currencyCode,
                'en'
            ),
        };
    }

    public function find(string $code): ?array
    {
        $code = strtoupper(trim($code));

        foreach ($this->all() as $currency) {
            if ($currency['code'] === $code) {
                return $currency;
            }
        }

        return null;
    }

    public function exists(string $code): bool
    {
        return Currencies::exists(
            strtoupper(trim($code))
        );
    }
}
