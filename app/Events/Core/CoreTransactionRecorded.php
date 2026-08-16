<?php

namespace App\Events\Core;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoreTransactionRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $sourceType,
        public readonly int|string|null $sourceId,
        public readonly string $transactionType,
        public readonly float $amount,
        public readonly string $currency = 'NGN',
        public readonly array $data = [],
    ) {
    }
}
