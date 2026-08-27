<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\CentralApi\CentralWebsiteDetailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Central Admin website registry.
     *
     * This page lists both SaaS and off-server websites from the
     * canonical websites registry.
     */
    public function index(Request $request): View
    {
        $query = Website::query()
            ->with([
                'owner',
                'developer',
                'plan',
                'workspace',
            ]);

        /*
         * --------------------------------------------------------
         * Search
         * --------------------------------------------------------
         */
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $query->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'website_uuid',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'registered_domain',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'domain',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'owner',
                            function ($ownerQuery) use ($search) {
                                $ownerQuery
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                }
            );
        }

        /*
         * --------------------------------------------------------
         * Deployment filter
         * --------------------------------------------------------
         */
        $deployment =
            trim(
                (string) $request->query(
                    'deployment',
                    ''
                )
            );

        if (
            in_array(
                $deployment,
                [
                    Website::DEPLOYMENT_SAAS,
                    Website::DEPLOYMENT_OFF_SERVER,
                ],
                true
            )
        ) {
            $query->where(
                'deployment_type',
                $deployment
            );
        }

        /*
         * --------------------------------------------------------
         * Registry status filter
         * --------------------------------------------------------
         */
        $registryStatus =
            trim(
                (string) $request->query(
                    'registry_status',
                    ''
                )
            );

        if ($registryStatus !== '') {
            $query->where(
                'registry_status',
                $registryStatus
            );
        }

        /*
         * --------------------------------------------------------
         * Website runtime status filter
         * --------------------------------------------------------
         */
        $status =
            trim(
                (string) $request->query(
                    'status',
                    ''
                )
            );

        if ($status !== '') {
            $query->where(
                'status',
                $status
            );
        }

        $websites =
            $query
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();

        return view(
            'admin.websites.index',
            [
                'websites' =>
                    $websites,

                'filters' => [
                    'search' =>
                        $search,

                    'deployment' =>
                        $deployment,

                    'registry_status' =>
                        $registryStatus,

                    'status' =>
                        $status,
                ],
            ]
        );
    }


    /**
     * Canonical Central management snapshot for one website.
     */
    public function show(
        Website $website,
        CentralWebsiteDetailService $detailService
    ): View {
        $website->load([
            'owner',
            'developer',
            'plan',
            'workspace',
        ]);

        $detail =
            $detailService->get(
                $website
            );

        return view(
            'admin.websites.show',
            [
                'website' =>
                    $website,

                'detail' =>
                    $detail,
            ]
        );
    }


    public function toggle(
        Website $website
    ) {
        $website->forceFill([
            'user_enabled' =>
                !(bool) $website->user_enabled,
        ])->save();

        return back()->with(
            'success',
            $website->user_enabled
                ? 'Website enabled successfully.'
                : 'Website disabled successfully.'
        );
    }


    public function destroy(
        Website $website,
        \App\Services\Website\WebsiteDeletionService $deletion
    ) {
        $name =
            $website->name;

        $deletion->delete(
            $website
        );

        return redirect()
            ->route(
                'admin.websites.index'
            )
            ->with(
                'success',
                "{$name} was permanently deleted."
            );
    }

}