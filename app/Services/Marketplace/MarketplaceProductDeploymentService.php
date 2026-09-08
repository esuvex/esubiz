<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ESUBIZ_MARKETPLACE_PRODUCT_DEPLOYMENT_SERVICE_V1
 *
 * Central deployment-state service for Marketplace products that must be
 * installed into an Esubiz Core instance.
 *
 * Commercial fulfilment creates entitlement/license first, then calls
 * createPending(). The target Core can later claim the deployment,
 * perform validated installation, and report completion/failure.
 *
 * Supported deployment contexts:
 * - saas
 * - off_server
 */
class MarketplaceProductDeploymentService
{
    protected string $table = 'marketplace_product_deployments';

    public function createPending(array $data): array
    {
        $productType = $this->normalizeType(
            (string) ($data['product_type'] ?? '')
        );

        if (!in_array(
            $productType,
            ['theme', 'module', 'addon', 'bundle'],
            true
        )) {
            throw new RuntimeException(
                "Unsupported Core deployment product type [{$productType}]."
            );
        }

        $deploymentType = strtolower(
            trim(
                (string) (
                    $data['deployment_type']
                    ?? $data['deployment']
                    ?? ''
                )
            )
        );

        if (!in_array(
            $deploymentType,
            ['saas', 'off_server'],
            true
        )) {
            throw new RuntimeException(
                'Marketplace deployment requires deployment_type=saas or off_server.'
            );
        }

        $entitlementId = (int) (
            $data['entitlement_id']
            ?? 0
        );

        if ($entitlementId <= 0) {
            throw new RuntimeException(
                'Marketplace deployment requires entitlement_id.'
            );
        }

        if (
            $deploymentType === 'off_server'
            && (int) ($data['license_id'] ?? 0) <= 0
        ) {
            throw new RuntimeException(
                'Off-server Marketplace deployment requires license_id.'
            );
        }

        $action = strtolower(
            trim(
                (string) (
                    $data['action']
                    ?? 'install'
                )
            )
        );

        if (!in_array(
            $action,
            ['install', 'update'],
            true
        )) {
            throw new RuntimeException(
                "Unsupported deployment action [{$action}]."
            );
        }

        $existing = DB::table($this->table)
            ->where('entitlement_id', $entitlementId)
            ->where('product_type', $productType)
            ->where('action', $action)
            ->where('deployment_type', $deploymentType)
            ->first();

        if ($existing) {
            return $this->normalizeRow($existing);
        }

        $now = now();

        $id = DB::table($this->table)->insertGetId([
            'uuid' => (string) Str::uuid(),

            'user_id' =>
                $this->nullableInteger(
                    $data['user_id'] ?? null
                ),

            'website_id' =>
                $this->nullableInteger(
                    $data['website_id'] ?? null
                ),

            'marketplace_order_id' =>
                $this->nullableInteger(
                    $data['marketplace_order_id']
                    ?? $data['order_id']
                    ?? null
                ),

            'marketplace_listing_id' =>
                $this->nullableInteger(
                    $data['marketplace_listing_id']
                    ?? null
                ),

            'catalog_product_id' =>
                $this->nullableInteger(
                    $data['catalog_product_id']
                    ?? null
                ),

            'entitlement_id' => $entitlementId,

            'license_id' =>
                $this->nullableInteger(
                    $data['license_id'] ?? null
                ),

            'product_type' => $productType,

            'product_slug' =>
                $this->nullableString(
                    $data['product_slug']
                    ?? $data['slug']
                    ?? null
                ),

            'product_version' =>
                $this->nullableString(
                    $data['product_version']
                    ?? $data['version']
                    ?? null
                ),

            'deployment_type' => $deploymentType,
            'action' => $action,
            'status' => 'pending',
            'attempts' => 0,

            'package_reference' =>
                $this->nullableString(
                    $data['package_reference']
                    ?? null
                ),

            'checksum_sha256' =>
                $this->nullableString(
                    $data['checksum_sha256']
                    ?? null
                ),

            'manifest' =>
                $this->encodeJson(
                    $data['manifest']
                    ?? null
                ),

            'context' =>
                $this->encodeJson(
                    $data['context']
                    ?? null
                ),

            'result' => null,
            'last_error' => null,
            'claimed_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'failed_at' => null,

            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->findById($id);
    }

    public function findById(int $id): array
    {
        $row = DB::table($this->table)
            ->where('id', $id)
            ->first();

        if (!$row) {
            throw new RuntimeException(
                "Marketplace deployment [{$id}] was not found."
            );
        }

        return $this->normalizeRow($row);
    }

    public function findByUuid(string $uuid): array
    {
        $row = DB::table($this->table)
            ->where('uuid', trim($uuid))
            ->first();

        if (!$row) {
            throw new RuntimeException(
                'Marketplace deployment was not found.'
            );
        }

        return $this->normalizeRow($row);
    }

    public function claim(
        int $id,
        array $context = []
    ): array {
        return DB::transaction(function () use (
            $id,
            $context
        ) {
            $row = DB::table($this->table)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                throw new RuntimeException(
                    "Marketplace deployment [{$id}] was not found."
                );
            }

            if ($row->status === 'completed') {
                return $this->normalizeRow($row);
            }

            if (!in_array(
                $row->status,
                ['pending', 'failed'],
                true
            )) {
                throw new RuntimeException(
                    "Marketplace deployment [{$id}] cannot be claimed from status [{$row->status}]."
                );
            }

            $mergedContext = array_merge(
                $this->decodeJson($row->context),
                $context
            );

            DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'status' => 'processing',
                    'attempts' => ((int) $row->attempts) + 1,
                    'claimed_at' => now(),
                    'started_at' => now(),
                    'failed_at' => null,
                    'last_error' => null,
                    'context' => $this->encodeJson($mergedContext),
                    'updated_at' => now(),
                ]);

            return $this->findById($id);
        });
    }

    public function complete(
        int $id,
        array $result = []
    ): array {
        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'status' => 'completed',
                'result' => $this->encodeJson($result),
                'last_error' => null,
                'completed_at' => now(),
                'failed_at' => null,
                'updated_at' => now(),
            ]);

        return $this->findById($id);
    }

    public function fail(
        int $id,
        \Throwable|string $error
    ): array {
        $message = $error instanceof \Throwable
            ? $error->getMessage()
            : (string) $error;

        DB::table($this->table)
            ->where('id', $id)
            ->update([
                'status' => 'failed',
                'last_error' => $message,
                'failed_at' => now(),
                'updated_at' => now(),
            ]);

        return $this->findById($id);
    }

    public function pendingForWebsite(
        int $websiteId
    ): array {
        return DB::table($this->table)
            ->where('website_id', $websiteId)
            ->whereIn(
                'status',
                ['pending', 'failed']
            )
            ->orderBy('id')
            ->get()
            ->map(
                fn ($row) =>
                    $this->normalizeRow($row)
            )
            ->all();
    }

    protected function normalizeType(
        string $type
    ): string {
        return strtolower(
            trim(
                str_replace('-', '_', $type)
            )
        );
    }

    protected function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    protected function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (int) $value;
    }

    protected function encodeJson(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new RuntimeException(
                'Marketplace deployment JSON data must be an array.'
            );
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );
    }

    protected function decodeJson(
        mixed $value
    ): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode(
            $value,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    protected function normalizeRow(
        object $row
    ): array {
        $data = (array) $row;

        foreach (
            ['manifest', 'context', 'result']
            as $field
        ) {
            $data[$field] =
                $this->decodeJson(
                    $data[$field] ?? null
                );
        }

        $data['attempts'] =
            (int) ($data['attempts'] ?? 0);

        return $data;
    }
}
