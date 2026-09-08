<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * ESUBIZ_MARKETPLACE_DEPLOYMENT_ACCESS_TOKEN_V1
 *
 * Short-purpose bearer credential for one Marketplace deployment.
 *
 * The plaintext token is returned only when issued. Central persistence
 * stores only its SHA-256 hash inside deployment context.
 *
 * Marketplace license keys remain licensing credentials and are never used
 * as deployment API bearer tokens.
 */
class MarketplaceDeploymentAccessTokenService
{
    public function issue(int $deploymentId): string
    {
        if ($deploymentId <= 0) {
            throw new RuntimeException(
                'A valid Marketplace deployment ID is required.'
            );
        }

        return DB::transaction(function () use ($deploymentId) {
            $deployment = DB::table('marketplace_product_deployments')
                ->where('id', $deploymentId)
                ->lockForUpdate()
                ->first();

            if (!$deployment) {
                throw new RuntimeException(
                    'Marketplace deployment was not found.'
                );
            }

            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);

            $context = $this->decodeJson(
                $deployment->context ?? null
            );

            $context['deployment_access'] = [
                'token_sha256' => $hash,
                'issued_at' => now()->toIso8601String(),
            ];

            DB::table('marketplace_product_deployments')
                ->where('id', $deploymentId)
                ->update([
                    'context' => json_encode(
                        $context,
                        JSON_UNESCAPED_SLASHES
                    ),
                    'updated_at' => now(),
                ]);

            return $token;
        });
    }

    public function validate(
        int $deploymentId,
        ?string $token
    ): bool {
        $token = trim((string) $token);

        if ($deploymentId <= 0 || $token === '') {
            return false;
        }

        $deployment = DB::table('marketplace_product_deployments')
            ->where('id', $deploymentId)
            ->first();

        if (!$deployment) {
            return false;
        }

        $context = $this->decodeJson(
            $deployment->context ?? null
        );

        $expected = trim(
            (string) (
                $context['deployment_access']['token_sha256']
                ?? ''
            )
        );

        if ($expected === '') {
            return false;
        }

        return hash_equals(
            $expected,
            hash('sha256', $token)
        );
    }

    public function revoke(int $deploymentId): void
    {
        DB::transaction(function () use ($deploymentId) {
            $deployment = DB::table('marketplace_product_deployments')
                ->where('id', $deploymentId)
                ->lockForUpdate()
                ->first();

            if (!$deployment) {
                return;
            }

            $context = $this->decodeJson(
                $deployment->context ?? null
            );

            unset($context['deployment_access']);

            DB::table('marketplace_product_deployments')
                ->where('id', $deploymentId)
                ->update([
                    'context' => json_encode(
                        $context,
                        JSON_UNESCAPED_SLASHES
                    ),
                    'updated_at' => now(),
                ]);
        });
    }

    protected function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? $decoded
            : [];
    }
}
