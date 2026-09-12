<?php

namespace App\Listeners;

use App\Services\Platform\CentralVisitorCurrencyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PersistCentralRegistrationCountry
{
    /*
     * ESUBIZ_REGISTRATION_COUNTRY_PERSISTENCE_V7
     *
     * After successful Central registration:
     *
     * - save the country selected on the registration form;
     * - if unchanged, that value is the IP-detected default;
     * - manual registration selection wins over IP detection;
     * - persist the ISO country code on the new user's profile;
     * - keep the session currency context synchronized.
     */
    public function __construct(
        protected CentralVisitorCurrencyService $visitorCurrency
    ) {
    }

    public function handle(
        Registered $event
    ): void {
        $user = $event->user;

        if (!$user instanceof Model) {
            return;
        }

        try {
            $request = request();

            $country =
                $this->visitorCurrency
                    ->normalizeCountry(
                        $request->input(
                            'esubiz_country'
                        )
                        ?: $request->input(
                            'country'
                        )
                        ?: (
                            $request->hasSession()
                                ? $request->session()->get(
                                    'esubiz_country'
                                )
                                : null
                        )
                    );

            /*
             * If the registration request itself did not
             * explicitly contain a country, resolve the same
             * visitor country that was used to preselect the
             * registration field.
             */
            if ($country === null) {
                $country =
                    $this->visitorCurrency
                        ->resolveCountry(
                            $request
                        );
            }

            $country =
                $this->visitorCurrency
                    ->normalizeCountry(
                        $country
                    );

            if ($country === null) {
                return;
            }

            /*
             * Prefer dedicated ISO-country columns.
             *
             * We only use the generic `country` column if the
             * more explicit fields do not exist.
             */
            $table = $user->getTable();

            $countryField = null;

            foreach (
                [
                    'country_code',
                    'default_country_code',
                    'country',
                ] as $candidate
            ) {
                if (
                    Schema::hasColumn(
                        $table,
                        $candidate
                    )
                ) {
                    $countryField =
                        $candidate;

                    break;
                }
            }

            if ($countryField !== null) {
                $user->setAttribute(
                    $countryField,
                    $country
                );

                if ($user->isDirty()) {
                    $user->save();
                }
            }

            if ($request->hasSession()) {
                $request->session()->put(
                    'esubiz_country',
                    $country
                );
            }
        } catch (\Throwable $exception) {
            /*
             * Country persistence must never prevent account
             * creation if an unexpected environment/schema
             * issue occurs.
             */
        }
    }
}
