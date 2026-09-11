<?php

namespace App\Services\Platform;

use Carbon\Carbon;
use DateTimeZone;

class CentralTimezoneService
{
    public const DEFAULT_TIMEZONE =
        'Africa/Lagos';

    public function __construct(
        protected CentralSiteSettingsService $settings
    ) {
    }

    public function timezone(): string
    {
        $timezone = trim(
            (string) $this->settings->get(
                'platform.timezone',
                self::DEFAULT_TIMEZONE
            )
        );

        if (
            $timezone !== ''
            && in_array(
                $timezone,
                DateTimeZone::listIdentifiers(),
                true
            )
        ) {
            return $timezone;
        }

        return self::DEFAULT_TIMEZONE;
    }

    public function fromUtc(
        Carbon|string|null $value
    ): ?Carbon {
        if ($value === null || $value === '') {
            return null;
        }

        $carbon = $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse(
                $value,
                'UTC'
            );

        return $carbon
            ->utc()
            ->setTimezone(
                $this->timezone()
            );
    }

    public function toUtc(
        Carbon|string|null $value
    ): ?Carbon {
        if ($value === null || $value === '') {
            return null;
        }

        $carbon = $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse(
                $value,
                $this->timezone()
            );

        return $carbon->utc();
    }

    public function now(): Carbon
    {
        return Carbon::now(
            $this->timezone()
        );
    }
}
