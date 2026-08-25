<?php

namespace App\Services\SiteAi\Providers;

use App\Services\SiteAi\Contracts\SiteAiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ CENTRAL SITE AI PROVIDER
 * ============================================================
 *
 * Every SaaS tenant and future off-server website sends AI
 * work through the central Esubiz AI API.
 *
 * Website features never receive:
 *
 * - OpenAI API keys
 * - provider credentials
 * - model credentials
 * - AI credit balances
 *
 * Those remain under central Esubiz control.
 */
class EsubizCentralSiteAiProvider implements SiteAiProvider
{
    public function generate(
        array $request
    ): array {

        $url =
            trim(
                (string) config(
                    'services.esubiz_site_ai.url'
                )
            );

        if ($url === '') {
            throw new RuntimeException(
                'Esubiz Central Site AI endpoint is not configured.'
            );
        }


        $token =
            trim(
                (string) config(
                    'services.esubiz_site_ai.token'
                )
            );


        try {

            $http =
                Http::acceptJson()
                    ->asJson()
                    ->timeout(
                        (int) config(
                            'services.esubiz_site_ai.timeout',
                            120
                        )
                    );


            if ($token !== '') {
                $http =
                    $http->withToken(
                        $token
                    );
            }


            $response =
                $http->post(
                    $url,
                    $request
                );


        } catch (
            ConnectionException $exception
        ) {

            throw new RuntimeException(
                'Could not connect to Esubiz Central AI.',
                previous: $exception
            );
        }


        if (!$response->successful()) {

            $message =
                $response->json(
                    'message'
                )
                ?? 'Esubiz Central AI request failed.';


            throw new RuntimeException(
                $message
            );
        }


        $result =
            $response->json();


        if (!is_array($result)) {
            throw new RuntimeException(
                'Esubiz Central AI returned an invalid response.'
            );
        }


        return $result;
    }
}
