<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\SiteAi\SiteAiEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

/**
 * Generic tenant-facing AI endpoint.
 *
 * Theme Config, Page Builder, Ecommerce, Hotel, Blog,
 * Forms and future website features all call this endpoint.
 */
class TenantSiteAiController extends Controller
{
    public function generate(
        Request $request,
        string $subdomain,
        SiteAiEngine $engine
    ): JsonResponse {

        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $data =
            $request->validate([
                'capability' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'action' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'prompt' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'payload' => [
                    'nullable',
                    'array',
                ],

                'context' => [
                    'nullable',
                    'array',
                ],
            ]);


        $payload =
            (array) (
                $data[
                    'payload'
                ] ?? []
            );


        /*
         * Prompt is normalized into payload so all capabilities
         * receive the same canonical request structure.
         */
        if (
            array_key_exists(
                'prompt',
                $data
            )
        ) {
            $payload[
                'prompt'
            ] =
                $data[
                    'prompt'
                ];
        }


        try {

            $result =
                $engine->generate(
                    $website,
                    $data[
                        'capability'
                    ],
                    $data[
                        'action'
                    ],
                    $payload,
                    (array) (
                        $data[
                            'context'
                        ] ?? []
                    )
                );


            return response()->json([
                'success' =>
                    true,

                'capability' =>
                    $data[
                        'capability'
                    ],

                'action' =>
                    $data[
                        'action'
                    ],

                'result' =>
                    $result,
            ]);


        } catch (
            RuntimeException $exception
        ) {

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        $exception
                            ->getMessage(),
                ],
                422
            );


        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Site AI could not complete this request.',
                ],
                500
            );
        }
    }
}
