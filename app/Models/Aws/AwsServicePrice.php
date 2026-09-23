<?php

namespace App\Models\Aws;

use Illuminate\Database\Eloquent\Model;

class AwsServicePrice extends Model
{
    protected $table = 'aws_service_prices';

    protected $fillable = [
        'provider',
        'service',
        'price_key',
        'service_region',
        'amount',
        'currency',
        'unit',
        'quantity',
        'normalized_unit_amount',
        'service_code',
        'sku',
        'rate_code',
        'usage_type',
        'operation',
        'location',
        'description',
        'provider_payload',
        'provider_effective_at',
        'synced_at',
        'last_success_at',
        'last_error',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:12',
        'quantity' => 'decimal:6',
        'normalized_unit_amount' => 'decimal:12',
        'provider_payload' => 'array',
        'provider_effective_at' => 'datetime',
        'synced_at' => 'datetime',
        'last_success_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isFresh(
        int $minutes = 1440
    ): bool {
        if (
            !$this->is_active
            || $this->last_success_at === null
        ) {
            return false;
        }

        return $this->last_success_at
            ->greaterThanOrEqualTo(
                now()->subMinutes($minutes)
            );
    }
}
