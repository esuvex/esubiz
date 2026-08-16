<?php

namespace App\Listeners\Core;

use App\Events\Core\CoreTransactionRecorded;
use App\Listeners\Core\ProcessCoreRevenueEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordCoreRevenueEvent
{
    public function handle(CoreTransactionRecorded $event): void
    {
        $id = DB::table('revenue_events')->insertGetId([
            'workspace_id' => $event->data['workspace_id'] ?? null,
            'uuid' => (string) Str::uuid(),

            'source_module' => $event->sourceType,
            'event_type' => $event->transactionType,

            'reference_type' => $event->data['reference_type'] ?? null,
            'reference_id' => $event->sourceId,

            'user_id' => $event->data['customer_id']
                ?? $event->data['user_id']
                ?? null,

            'gross_amount' => $event->amount,
            'discount_amount' => $event->data['discount_amount'] ?? 0,
            'tax_amount' => $event->data['tax_amount'] ?? 0,
            'net_amount' => $event->data['net_amount'] ?? $event->amount,

            'currency' => $event->currency,

            'is_commissionable' => $event->data['commissionable'] ?? true,
            'is_processed' => false,

            'status' => 'pending',

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $revenueEvent = DB::table('revenue_events')
            ->where('id', $id)
            ->first();

        if ($revenueEvent) {
            app(ProcessCoreRevenueEvent::class)
                ->process($revenueEvent);
        }
    }
}
