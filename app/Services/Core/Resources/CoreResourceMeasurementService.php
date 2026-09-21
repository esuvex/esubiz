<?php

namespace App\Services\Core\Resources;

use App\Services\Core\CoreAddonSalesTriggerResolver;
use App\Services\Core\CoreEntitlementService;
use Illuminate\Support\Collection;
use Throwable;

class CoreResourceMeasurementService
{
    public function __construct(
        protected CoreEntitlementService $entitlements,
        protected CoreAddonSalesTriggerResolver $salesTriggers
    ) {
    }

    /**
     * Universal, fail-safe Core resource measurement.
     *
     * SaaS:
     *   current usage / live Central limit + Allocation Add-ons
     *
     * Off-server:
     *   current usage / Unlimited
     *
     * Missing/unbuilt resources resolve safely instead of breaking a Core page.
     */
    public function measure(
        string $resourceKey,
        object $website,
        ?string $salesTriggerLocation = null
    ): array {
        $resourceKey = trim($resourceKey);
        $websiteId = (int) ($website->id ?? 0);

        $result = [
            'resource_key' => $resourceKey,
            'resolved' => false,
            'usage' => null,
            'limit' => null,
            'is_unlimited' => false,
            'usage_text' => null,
            'percentage' => null,
            'percentage_text' => null,
            'deployment_type' => $this->deploymentType($website),
            'sales_triggers' => collect(),
        ];

        if ($resourceKey === '' || $websiteId <= 0) {
            return $result;
        }

        try {
            $usage = $this->entitlements->currentUsage(
                $resourceKey,
                $websiteId
            );

            $limit = $this->entitlements->effectiveAllocation(
                $resourceKey,
                $websiteId
            );

            $unlimited = $limit === null;

            $percentage = null;

            if (!$unlimited && (float) $limit > 0) {
                $percentage = min(
                    100,
                    round(
                        ((float) $usage / (float) $limit) * 100,
                        2
                    )
                );
            }

            $result = array_merge($result, [
                'resolved' => true,
                'usage' => $usage,
                'limit' => $limit,
                'is_unlimited' => $unlimited,
                'usage_text' => $this->number($usage)
                    .' / '
                    .($unlimited ? 'Unlimited' : $this->number($limit)),
                'percentage' => $percentage,
                'percentage_text' => $percentage !== null
                    ? $this->number($percentage).'%'
                    : null,
            ]);
        } catch (Throwable) {
            /*
             * Plug-and-play rule:
             * missing table, usage source, unfinished Core page/module or other
             * unresolved resource must never produce a 500.
             */
        }

        if ($salesTriggerLocation !== null && $salesTriggerLocation !== '') {
            try {
                $result['sales_triggers'] = $this->salesTriggers->resolve(
                    $salesTriggerLocation,
                    $website,
                    [
                        'deployment_type' => $result['deployment_type'],
                        'resources' => [
                            $resourceKey => $result,
                        ],
                    ]
                );
            } catch (Throwable) {
                $result['sales_triggers'] = collect();
            }
        }

        return $result;
    }

    /**
     * Measure multiple resources without allowing one unfinished resource
     * to affect the others.
     */
    public function measureMany(
        array $resourceKeys,
        object $website,
        ?string $salesTriggerLocation = null
    ): Collection {
        return collect($resourceKeys)
            ->mapWithKeys(function ($resourceKey) use (
                $website,
                $salesTriggerLocation
            ) {
                $resourceKey = trim((string) $resourceKey);

                if ($resourceKey === '') {
                    return [];
                }

                return [
                    $resourceKey => $this->measure(
                        $resourceKey,
                        $website,
                        $salesTriggerLocation
                    ),
                ];
            });
    }

    protected function deploymentType(object $website): string
    {
        try {
            if (
                method_exists($website, 'isOffServer')
                && $website->isOffServer()
            ) {
                return 'off_server';
            }
        } catch (Throwable) {
            //
        }

        return 'saas';
    }

    protected function number(int|float|null $value): string
    {
        if ($value === null) {
            return '0';
        }

        $number = (float) $value;

        return floor($number) === $number
            ? number_format($number, 0)
            : rtrim(rtrim(number_format($number, 2, '.', ','), '0'), '.');
    }
}
