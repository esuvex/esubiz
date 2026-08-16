<?php

namespace App\Listeners\Core;

use App\Events\Core\CoreTransactionRecorded;
use Illuminate\Support\Facades\Log;

class HandleCoreTransactionRecorded
{
    public function handle(CoreTransactionRecorded $event): void
    {
        Log::info('Core transaction recorded.', [
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
            'transaction_type' => $event->transactionType,
            'amount' => $event->amount,
            'currency' => $event->currency,
            'data' => $event->data,
        ]);
    }
}
