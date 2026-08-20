<?php

namespace App\Services\Core;

use App\Models\Website;
use App\Services\Website\WebsiteTenantDatabaseService;
use RuntimeException;

class CoreCrmEntitlementService
{
    public function __construct(
        protected CoreEntitlementService $entitlements,
        protected WebsiteTenantDatabaseService $tenantDatabase
    ) {
    }

    /**
     * Check whether a new CRM resource can be created
     * inside the website's dedicated tenant database.
     */
    public function allows(
        Website $website,
        string $limitKey,
        string $table
    ): bool {
        $this->validateTable($table);

        if (!$this->entitlements->featureEnabled('crm')) {
            return false;
        }

        $limit = $this->effectiveLimit($website, $limitKey);

        if (!$limit) {
            return true;
        }

        if ($limit['unlimited'] || $limit['value'] === null) {
            return true;
        }

        $this->tenantDatabase->connect($website);

        try {
            $currentUsage = $this->tenantDatabase
                ->connection()
                ->table($table)
                ->count();

            return $currentUsage < $limit['value'];
        } finally {
            $this->tenantDatabase->disconnect();
        }
    }

    /**
     * Enforce the CRM limit and throw a useful exception
     * when the website has reached its configured allowance.
     */
    public function enforce(
        Website $website,
        string $limitKey,
        string $table
    ): void {
        $this->validateTable($table);

        if (!$this->entitlements->featureEnabled('crm')) {
            throw new RuntimeException(
                'CRM is currently disabled for this website.'
            );
        }

        $limit = $this->effectiveLimit($website, $limitKey);

        if (!$limit || $limit['unlimited'] || $limit['value'] === null) {
            return;
        }

        $this->tenantDatabase->connect($website);

        try {
            $currentUsage = $this->tenantDatabase
                ->connection()
                ->table($table)
                ->count();

            if ($currentUsage >= $limit['value']) {
                throw new RuntimeException(
                    "{$limit['name']} limit reached. " .
                    "Your current effective limit is {$limit['value']} " .
                    ($limit['unit'] ?: 'records') . '.'
                );
            }
        } finally {
            $this->tenantDatabase->disconnect();
        }
    }

    protected function effectiveLimit(
        Website $website,
        string $limitKey
    ): ?array {
        $limit = $this->entitlements->limit('crm', $limitKey);

        if (!$limit) {
            return null;
        }

        if ($limit->is_unlimited || $limit->value_type === 'unlimited') {
            return [
                'name' => $limit->name,
                'unit' => $limit->unit,
                'value' => null,
                'unlimited' => true,
            ];
        }

        $base = $limit->default_value === null
            ? null
            : (int) $limit->default_value;

        if ($base === null) {
            return [
                'name' => $limit->name,
                'unit' => $limit->unit,
                'value' => null,
                'unlimited' => true,
            ];
        }

        $addonIds = DB::table('product_entitlements')
            ->where('website_id', $website->id)
            ->where('product_type', 'core_addon')
            ->where('status', 'active')
            ->pluck('product_id')
            ->unique()
            ->values();

        if ($addonIds->isEmpty()) {
            return [
                'name' => $limit->name,
                'unit' => $limit->unit,
                'value' => $base,
                'unlimited' => false,
            ];
        }

        $allocations = DB::table('core_addon_capability_allocations')
            ->whereIn('addon_id', $addonIds)
            ->where('capability_key', $limitKey)
            ->get(['allocation', 'is_unlimited']);

        if ($allocations->contains(
            fn ($row) => (bool) $row->is_unlimited
        )) {
            return [
                'name' => $limit->name,
                'unit' => $limit->unit,
                'value' => null,
                'unlimited' => true,
            ];
        }

        return [
            'name' => $limit->name,
            'unit' => $limit->unit,
            'value' => $base + (int) $allocations->sum(
                fn ($row) => (int) ($row->allocation ?? 0)
            ),
            'unlimited' => false,
        ];
    }

    /**
     * Return the current usage of a CRM resource.
     */
    public function usage(
        Website $website,
        string $table
    ): int {
        $this->validateTable($table);

        $this->tenantDatabase->connect($website);

        try {
            return (int) $this->tenantDatabase
                ->connection()
                ->table($table)
                ->count();
        } finally {
            $this->tenantDatabase->disconnect();
        }
    }

    /**
     * Only approved tenant CRM tables may be queried by
     * the entitlement service.
     *
     * This prevents accidental access to Esubiz platform
     * financial tables or arbitrary database tables.
     */
    protected function validateTable(string $table): void
    {
        $allowed = [
            'crm_contacts',
            'crm_leads',
            'crm_tasks',
        ];

        if (!in_array($table, $allowed, true)) {
            throw new RuntimeException(
                "CRM entitlement table [{$table}] is not allowed."
            );
        }
    }
}
