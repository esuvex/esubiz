<?php

namespace App\Listeners;

use App\Services\Platform\CentralCountryCatalog;
use Illuminate\Auth\Events\Registered;

class PersistCentralRegistrationCountry
{
    /**
     * ESUBIZ_CENTRAL_REGISTRATION_PROFILE_PERSISTENCE_V23
     *
     * Central registration is authoritative for the initial
     * country and phone details saved to the account.
     */
    public function handle(Registered $event): void
    {
        try {
            $request = request();

            if (!$request) {
                return;
            }

            /*
             * Do not apply Central account persistence to tenant/Core
             * registrations on *.esubiz.com.
             */
            $requestHost = strtolower(
                trim((string) $request->getHost())
            );

            $centralHost = strtolower(
                trim(
                    (string) (
                        parse_url(
                            (string) config('app.url'),
                            PHP_URL_HOST
                        )
                        ?: 'esubiz.com'
                    )
                )
            );

            $centralHosts = array_values(
                array_unique([
                    $centralHost,
                    preg_replace(
                        '/^www\./',
                        '',
                        $centralHost
                    ),
                    'www.' . preg_replace(
                        '/^www\./',
                        '',
                        $centralHost
                    ),
                ])
            );

            if (!in_array(
                $requestHost,
                $centralHosts,
                true
            )) {
                return;
            }

            $user = $event->user;

            if (!$user) {
                return;
            }

            $catalog = app(
                CentralCountryCatalog::class
            );

            $country = strtoupper(
                trim(
                    (string) $request->input(
                        'country_code',
                        $request->input(
                            'country',
                            ''
                        )
                    )
                )
            );

            if (!$catalog->has($country)) {
                return;
            }

            $phoneCountryCode =
                $catalog->dialCode(
                    $country,
                    '+234'
                );

            $phoneNumber = trim(
                (string) $request->input(
                    'phone_number',
                    $request->input('phone', '')
                )
            );

            /*
             * ESUBIZ_REGISTRATION_COUNTRY_PERSISTENCE_V23
             *
             * forceFill is intentional here so persistence does not
             * depend on User::$fillable.
             */
            $attributes = [
                'country_code' =>
                    $country,

                'phone_country_code' =>
                    $phoneCountryCode,
            ];

            if ($phoneNumber !== '') {
                $attributes['phone_number'] =
                    $phoneNumber;
            }

            $user->forceFill(
                $attributes
            )->save();

            /*
             * Saved profile country now outranks IP/default country
             * for the authenticated account.
             */
            if ($request->hasSession()) {
                $request->session()->put(
                    'esubiz_country_code',
                    $country
                );

                $request->session()->put(
                    'esubiz_country_source',
                    'profile'
                );
            }

        } catch (\Throwable $e) {
            report($e);
        }
    }
}
